<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function chat_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function chat_request_data(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    return is_array($data) ? $data : $_POST;
}

function chat_require_participant(PDO $pdo, int $conversationId, int $userId): void
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM chat_participants WHERE conversation_id = ? AND user_id = ? LIMIT 1'
    );
    $stmt->execute([$conversationId, $userId]);

    if (!$stmt->fetchColumn()) {
        chat_json(['ok' => false, 'error' => 'You do not have access to this conversation.'], 403);
    }
}

if (!is_logged_in()) {
    chat_json(['ok' => false, 'error' => 'Your session has expired. Please sign in again.'], 401);
}

$user = current_user();
if (!$user) {
    chat_json(['ok' => false, 'error' => 'Your account is unavailable. Please sign in again.'], 401);
}

$me = (int) $user['id'];
$pdo = db();
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');
$data = chat_request_data();

try {
    if ($method === 'GET' && $action === 'users') {
        $stmt = $pdo->prepare("
            SELECT u.id, u.first_name, u.last_name, u.email, r.name AS role
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.status = 'active' AND u.id <> ?
            ORDER BY u.first_name, u.last_name
        ");
        $stmt->execute([$me]);
        chat_json(['ok' => true, 'users' => $stmt->fetchAll()]);
    }

    if ($method === 'POST' && $action === 'start') {
        if (!hash_equals(csrf_token(), (string) ($data['csrf'] ?? ''))) {
            chat_json(['ok' => false, 'error' => 'Security token expired. Refresh the page and try again.'], 419);
        }

        $otherId = filter_var($data['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$otherId || $otherId === $me) {
            chat_json(['ok' => false, 'error' => 'Choose a valid person to message.'], 422);
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$otherId]);
        if (!$stmt->fetchColumn()) {
            chat_json(['ok' => false, 'error' => 'That user is not available.'], 404);
        }

        $stmt = $pdo->prepare("
            SELECT c.id
            FROM chat_conversations c
            INNER JOIN chat_participants p1 ON p1.conversation_id = c.id AND p1.user_id = ?
            INNER JOIN chat_participants p2 ON p2.conversation_id = c.id AND p2.user_id = ?
            WHERE c.conversation_type = 'direct'
              AND (SELECT COUNT(*) FROM chat_participants px WHERE px.conversation_id = c.id) = 2
            ORDER BY c.id ASC
            LIMIT 1
        ");
        $stmt->execute([$me, (int) $otherId]);
        $conversationId = (int) ($stmt->fetchColumn() ?: 0);

        if ($conversationId === 0) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO chat_conversations (conversation_type, created_by)
                    VALUES ('direct', ?)
                ");
                $stmt->execute([$me]);
                $conversationId = (int) $pdo->lastInsertId();

                $stmt = $pdo->prepare("
                    INSERT INTO chat_participants (conversation_id, user_id)
                    VALUES (?, ?), (?, ?)
                ");
                $stmt->execute([$conversationId, $me, $conversationId, (int) $otherId]);
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
        }

        chat_json(['ok' => true, 'conversation_id' => $conversationId]);
    }

    if ($method === 'GET' && $action === 'messages') {
        $conversationId = filter_var($_GET['conversation_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$conversationId || $conversationId < 1) {
            chat_json(['ok' => false, 'error' => 'Choose a conversation first.'], 422);
        }

        chat_require_participant($pdo, (int) $conversationId, $me);

        $afterId = filter_var($_GET['after_id'] ?? 0, FILTER_VALIDATE_INT);
        $afterId = max(0, (int) $afterId);

        $stmt = $pdo->prepare("
            SELECT m.id, m.conversation_id, m.sender_id, m.message_text, m.sent_at,
                   u.first_name, u.last_name
            FROM chat_messages m
            INNER JOIN users u ON u.id = m.sender_id
            WHERE m.conversation_id = ? AND m.id > ?
            ORDER BY m.id ASC
            LIMIT 200
        ");
        $stmt->execute([(int) $conversationId, $afterId]);
        $messages = $stmt->fetchAll();

        $stmt = $pdo->prepare("
            UPDATE chat_participants SET last_read_at = NOW()
            WHERE conversation_id = ? AND user_id = ?
        ");
        $stmt->execute([(int) $conversationId, $me]);

        chat_json(['ok' => true, 'messages' => $messages]);
    }

    if ($method === 'POST' && $action === 'send') {
        if (!hash_equals(csrf_token(), (string) ($data['csrf'] ?? ''))) {
            chat_json(['ok' => false, 'error' => 'Security token expired. Refresh the page and try again.'], 419);
        }

        $conversationId = filter_var($data['conversation_id'] ?? null, FILTER_VALIDATE_INT);
        $message = trim((string) ($data['message'] ?? ''));

        if (!$conversationId || $conversationId < 1) {
            chat_json(['ok' => false, 'error' => 'Choose a conversation first.'], 422);
        }
        if ($message === '') {
            chat_json(['ok' => false, 'error' => 'Type a message before sending.'], 422);
        }
        if (mb_strlen($message) > 5000) {
            chat_json(['ok' => false, 'error' => 'Messages must be 5,000 characters or fewer.'], 422);
        }

        chat_require_participant($pdo, (int) $conversationId, $me);

        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (conversation_id, sender_id, message_text)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([(int) $conversationId, $me, $message]);

        $stmt = $pdo->prepare("
            SELECT m.id, m.conversation_id, m.sender_id, m.message_text, m.sent_at,
                   u.first_name, u.last_name
            FROM chat_messages m
            INNER JOIN users u ON u.id = m.sender_id
            WHERE m.id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $pdo->lastInsertId()]);
        chat_json(['ok' => true, 'message' => $stmt->fetch()]);
    }

    // WebRTC signaling. Audio/video media travels peer-to-peer; this API relays setup messages.
    if ($method === 'POST' && in_array($action, ['call-start','call-signal','call-accept','call-end'], true)) {
        if (!hash_equals(csrf_token(), (string) ($data['csrf'] ?? ''))) {
            chat_json(['ok' => false, 'error' => 'Security token expired. Refresh the page and try again.'], 419);
        }

        if ($action === 'call-start') {
            $conversationId = filter_var($data['conversation_id'] ?? null, FILTER_VALIDATE_INT);
            $callType = (string)($data['call_type'] ?? 'voice');
            if (!$conversationId || $conversationId < 1 || !in_array($callType, ['voice','video'], true)) {
                chat_json(['ok'=>false,'error'=>'Choose a conversation and a valid call type.'], 422);
            }
            chat_require_participant($pdo, (int)$conversationId, $me);
            $stmt = $pdo->prepare('SELECT user_id FROM chat_participants WHERE conversation_id = ? AND user_id <> ? LIMIT 1');
            $stmt->execute([(int)$conversationId, $me]);
            $recipientId = (int)($stmt->fetchColumn() ?: 0);
            if (!$recipientId) chat_json(['ok'=>false,'error'=>'No other participant was found in this conversation.'], 422);
            $stmt = $pdo->prepare("INSERT INTO chat_call_sessions (conversation_id, started_by, call_type, status) VALUES (?, ?, ?, 'ringing')");
            $stmt->execute([(int)$conversationId, $me, $callType]);
            $callId = (int)$pdo->lastInsertId();
            chat_json(['ok'=>true,'call_id'=>$callId,'recipient_id'=>$recipientId]);
        }

        $callId = filter_var($data['call_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$callId || $callId < 1) chat_json(['ok'=>false,'error'=>'Invalid call.'], 422);
        $stmt = $pdo->prepare('SELECT id, conversation_id, started_by, call_type, status FROM chat_call_sessions WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$callId]);
        $call = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$call) chat_json(['ok'=>false,'error'=>'Call not found.'], 404);
        chat_require_participant($pdo, (int)$call['conversation_id'], $me);
        $stmt = $pdo->prepare('SELECT user_id FROM chat_participants WHERE conversation_id = ? AND user_id <> ? LIMIT 1');
        $stmt->execute([(int)$call['conversation_id'], $me]);
        $otherId = (int)($stmt->fetchColumn() ?: 0);
        if (!$otherId) chat_json(['ok'=>false,'error'=>'Call participant not found.'], 422);

        if ($action === 'call-signal') {
            $type = (string)($data['signal_type'] ?? '');
            $allowed = ['offer','answer','candidate'];
            if (!in_array($type, $allowed, true)) chat_json(['ok'=>false,'error'=>'Invalid signaling message.'], 422);
            $payload = $data['payload'] ?? null;
            if (!is_array($payload)) chat_json(['ok'=>false,'error'=>'Invalid signaling payload.'], 422);
            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false || strlen($encoded) > 100000) chat_json(['ok'=>false,'error'=>'Signaling payload is too large or invalid.'], 422);
            $stmt = $pdo->prepare('INSERT INTO chat_call_signals (call_id, sender_id, recipient_id, signal_type, payload) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([(int)$callId, $me, $otherId, $type, $encoded]);
            chat_json(['ok'=>true]);
        }

        if ($action === 'call-accept') {
            if ((int)$call['started_by'] === $me) chat_json(['ok'=>false,'error'=>'The caller cannot accept their own call.'], 422);
            if ($call['status'] !== 'ringing') chat_json(['ok'=>false,'error'=>'This call is no longer ringing.'], 409);
            $stmt = $pdo->prepare("UPDATE chat_call_sessions SET status = 'accepted' WHERE id = ? AND status = 'ringing'");
            $stmt->execute([(int)$callId]);
            chat_json(['ok'=>true]);
        }

        if ($action === 'call-end') {
            $signalType = (string)($data['signal_type'] ?? 'end');
            if (!in_array($signalType, ['decline','end'], true)) $signalType = 'end';
            $stmt = $pdo->prepare('INSERT INTO chat_call_signals (call_id, sender_id, recipient_id, signal_type, payload) VALUES (?, ?, ?, ?, NULL)');
            $stmt->execute([(int)$callId, $me, $otherId, $signalType]);
            // Status names here match the call-session enum described in the project schema.
            $newStatus = $signalType === 'decline' ? 'declined' : 'ended';
            $stmt = $pdo->prepare('UPDATE chat_call_sessions SET status = ?, ended_at = NOW() WHERE id = ?');
            $stmt->execute([$newStatus, (int)$callId]);
            chat_json(['ok'=>true]);
        }
    }

    if ($method === 'GET' && $action === 'call-poll') {
        $afterId = max(0, (int)($_GET['after_id'] ?? 0));
        $callId = filter_var($_GET['call_id'] ?? null, FILTER_VALIDATE_INT);
        if ($callId) {
            $stmt = $pdo->prepare('SELECT id, conversation_id, started_by, call_type, status FROM chat_call_sessions WHERE id = ? LIMIT 1');
            $stmt->execute([(int)$callId]);
            $call = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$call) chat_json(['ok'=>false,'error'=>'Call not found.'], 404);
            chat_require_participant($pdo, (int)$call['conversation_id'], $me);
            $stmt = $pdo->prepare('SELECT id, sender_id, signal_type, payload, created_at FROM chat_call_signals WHERE call_id = ? AND recipient_id = ? AND id > ? ORDER BY id ASC LIMIT 100');
            $stmt->execute([(int)$callId, $me, $afterId]);
            $signals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($signals as &$signal) { $signal['payload'] = $signal['payload'] ? json_decode($signal['payload'], true) : null; }
            unset($signal);
            chat_json(['ok'=>true,'call'=>$call,'signals'=>$signals]);
        }
        // Incoming ringing calls for the current user.
        $stmt = $pdo->prepare("SELECT c.id AS call_id, c.conversation_id, c.started_by, c.call_type, c.status, u.first_name, u.last_name FROM chat_call_sessions c INNER JOIN chat_participants p ON p.conversation_id = c.conversation_id AND p.user_id = ? INNER JOIN users u ON u.id = c.started_by WHERE c.started_by <> ? AND c.status = 'ringing' AND c.started_at >= (NOW() - INTERVAL 2 MINUTE) ORDER BY c.id DESC LIMIT 5");
        $stmt->execute([$me, $me]);
        chat_json(['ok'=>true,'incoming'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    chat_json(['ok' => false, 'error' => 'Unknown chat request.'], 404);
} catch (Throwable $exception) {
    error_log('EduBridge chat API error: ' . $exception->getMessage());
    chat_json(['ok' => false, 'error' => 'A server error occurred. Check the PHP error log if the problem continues.'], 500);
}
