<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$user = current_user();

if (!$user) {
    redirect('login.php');
}

$page_title = 'Messages';

try {
    $stmt = db()->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, r.name AS role
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        WHERE u.status = 'active' AND u.id <> ?
        ORDER BY u.first_name ASC, u.last_name ASC
    ");
    $stmt->execute([(int) $user['id']]);
    $users = $stmt->fetchAll();
    $loadError = false;
} catch (Throwable $exception) {
    error_log('EduBridge chat user list error: ' . $exception->getMessage());
    $users = [];
    $loadError = true;
}

function chat_initials(string $first, string $last): string
{
    $firstInitial = mb_substr(trim($first), 0, 1);
    $lastInitial = mb_substr(trim($last), 0, 1);
    return strtoupper(($firstInitial ?: '') . ($lastInitial ?: 'EB'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($page_title) ?> | ITayo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/edubridge.css?v=20261017_softglass1">
    <style>
        :root{--primary:#762d59;--primary-dark:#54203f;--soft:#f8edf4;--bg:#f5f6fa;--border:#e8e8ef;--text:#252437;--muted:#77788b}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,sans-serif}
        .chat-page{max-width:1450px;margin:0 auto;padding:28px}.chat-heading{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px}
        .chat-heading h1{margin:0 0 7px;font-size:clamp(25px,3vw,34px);font-weight:800}.chat-heading p{margin:0;color:var(--muted);font-size:14px}
        .back-link{display:inline-flex;align-items:center;gap:9px;padding:11px 16px;border:1px solid var(--border);border-radius:12px;background:white;color:var(--primary);font-size:13px;font-weight:700;text-decoration:none}
        .chat-layout{display:grid;grid-template-columns:320px minmax(0,1fr);min-height:650px;overflow:hidden;background:white;border:1px solid var(--border);border-radius:20px;box-shadow:0 12px 35px rgba(35,25,50,.06)}
        .chat-sidebar{padding:22px;border-right:1px solid var(--border)}.profile-card{display:flex;align-items:center;gap:12px;padding:15px;margin-bottom:25px;background:var(--soft);border-radius:15px}
        .avatar{width:43px;height:43px;flex:0 0 43px;display:grid;place-items:center;border-radius:50%;background:var(--primary);color:white;font-weight:800;font-size:12px}
        .profile-info,.user-info{min-width:0}.profile-info strong,.profile-info span,.user-info strong,.user-info span{display:block;overflow-wrap:anywhere}
        .profile-info strong{font-size:13px}.profile-info span{margin-top:5px;color:var(--muted);font-size:11px;text-transform:capitalize}
        .section-heading{margin:0 0 13px;font-size:13px;font-weight:800}.search-box{width:100%;padding:12px 13px;margin-bottom:15px;border:1px solid var(--border);border-radius:11px;outline:none;font:inherit;font-size:13px}
        .search-box:focus,.message-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(118,45,89,.09)}
        .user-list{display:grid;gap:8px;max-height:470px;overflow-y:auto}.user-item{display:flex;align-items:center;gap:11px;width:100%;padding:12px;border:1px solid transparent;border-radius:13px;background:white;text-align:left;cursor:pointer}
        .user-item:hover,.user-item.active{background:#fbf7fa;border-color:#f0dfeb}.user-info strong{font-size:12px}.user-info span{margin-top:4px;color:var(--muted);font-size:11px;text-transform:capitalize}
        .empty-users{padding:20px 8px;color:var(--muted);font-size:13px;line-height:1.7;text-align:center}
        .chat-main{display:flex;flex-direction:column;min-width:0;min-height:650px}.chat-topbar{display:flex;align-items:center;gap:12px;padding:18px 24px;border-bottom:1px solid var(--border)}
        .chat-topbar h2{margin:0 0 5px;font-size:15px}.chat-topbar p{margin:0;color:var(--muted);font-size:12px}
        .messages{flex:1;display:flex;flex-direction:column;gap:12px;padding:22px;overflow-y:auto;min-height:400px;max-height:520px;background:#fdfcfe}
        .welcome-area{flex:1;display:grid;place-content:center;justify-items:center;padding:35px;text-align:center}.welcome-icon{width:82px;height:82px;display:grid;place-items:center;margin-bottom:22px;border-radius:25px;background:var(--soft);color:var(--primary);font-size:32px}
        .welcome-area h2{margin:0 0 10px;font-size:22px}.welcome-area p{max-width:380px;margin:0;color:var(--muted);font-size:13px;line-height:1.8}
        .bubble-row{display:flex;flex-direction:column;max-width:min(78%,560px);align-self:flex-start;gap:5px}.bubble-row.mine{align-self:flex-end;align-items:flex-end}
        .bubble-name{font-size:11px;color:var(--muted);padding:0 5px}.bubble{padding:11px 14px;border-radius:15px 15px 15px 4px;background:white;border:1px solid var(--border);font-size:13px;line-height:1.55;white-space:pre-wrap;overflow-wrap:anywhere}
        .mine .bubble{background:var(--primary);border-color:var(--primary);color:white;border-radius:15px 15px 4px 15px}.bubble-time{font-size:10px;color:var(--muted);padding:0 5px}
        .composer{display:flex;gap:10px;padding:16px 20px;border-top:1px solid var(--border);background:white}.message-input{flex:1;min-width:0;padding:13px 15px;border:1px solid var(--border);border-radius:12px;outline:none;font:inherit;font-size:13px}
        .send-button{padding:0 22px;border:0;border-radius:12px;background:var(--primary);color:white;font-weight:700;cursor:pointer}.send-button:hover{background:var(--primary-dark)}.send-button:disabled,.message-input:disabled{opacity:.55;cursor:not-allowed}
        .notice{display:none;padding:10px 15px;margin:0 20px 12px;border-radius:10px;background:#fff2f0;color:#a12828;font-size:12px}.notice.show{display:block}
        .hidden{display:none!important}
        .call-actions{display:flex;gap:8px;margin-left:auto;flex-wrap:wrap}.call-btn{border:0;border-radius:10px;padding:9px 12px;background:var(--primary);color:#fff;font:600 12px Inter,sans-serif;cursor:pointer}.call-btn.secondary{background:#f1e5ed;color:var(--primary)}.call-btn.danger{background:#b42318}.call-panel{position:fixed;z-index:9999;right:20px;bottom:20px;width:min(520px,calc(100vw - 32px));padding:16px;background:white;border:1px solid var(--border);border-radius:18px;box-shadow:0 20px 70px #1c102633}.call-videos{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0}.call-videos video{width:100%;min-height:130px;max-height:260px;object-fit:cover;background:#211a24;border-radius:12px}.call-status{font-size:13px;color:var(--muted)}.incoming-call{position:fixed;z-index:10000;top:18px;left:50%;transform:translateX(-50%);width:min(420px,calc(100vw - 24px));padding:16px;background:white;border:1px solid var(--border);border-radius:16px;box-shadow:0 15px 50px #1c102633}
        @media(max-width:800px){.chat-page{padding:16px}.chat-layout{grid-template-columns:1fr}.chat-sidebar{border-right:0;border-bottom:1px solid var(--border)}.user-list{max-height:230px}.chat-main{min-height:540px}.messages{min-height:330px;max-height:480px}.composer{padding:12px}.send-button{padding:0 16px}}
    </style>
    <meta name="theme-color" content="#e8ebef">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/itayo-soft-glass.css?v=20261019_glass1">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/itayo-chat-glass.css?v=20261019_chat1">
</head>
<body class="itayo-glass itayo-chat">
<main class="chat-page">
    <header class="chat-heading">
        <div><h1>Messages</h1><p>Connect and collaborate with your ITayo community.</p></div>
        <a class="back-link" href="<?= e(APP_URL . '/' . ltrim(role_home((string) $user['role']), '/')) ?>"><span aria-hidden="true">←</span> Back to Dashboard</a>
    </header>
    <section class="chat-layout">
        <aside class="chat-sidebar">
            <div class="profile-card">
                <div class="avatar"><?= e(chat_initials((string)($user['first_name']??''),(string)($user['last_name']??''))) ?></div>
                <div class="profile-info">
                    <strong><?= e(trim((string)($user['first_name']??'').' '.(string)($user['last_name']??''))) ?></strong>
                    <span><?= e((string)$user['role']) ?> · Your account</span>
                </div>
            </div>
            <h3 class="section-heading">People</h3>
            <input type="search" id="userSearch" class="search-box" placeholder="Search people..." aria-label="Search people">
            <?php if ($loadError): ?>
                <p class="empty-users">We couldn't load the user list. Please refresh the page.</p>
            <?php elseif (!$users): ?>
                <p class="empty-users">No other active users were found.</p>
            <?php else: ?>
                <div class="user-list" id="userList">
                <?php foreach ($users as $person): $fullName = trim((string)$person['first_name'].' '.(string)$person['last_name']); ?>
                    <button type="button" class="user-item" data-user-id="<?= (int)$person['id'] ?>" data-search="<?= e(mb_strtolower($fullName.' '.$person['email'].' '.$person['role'])) ?>">
                        <div class="avatar"><?= e(chat_initials((string)$person['first_name'],(string)$person['last_name'])) ?></div>
                        <div class="user-info"><strong><?= e($fullName) ?></strong><span><?= e((string)$person['role']) ?></span></div>
                    </button>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </aside>
        <section class="chat-main">
            <div class="chat-topbar">
                <div class="avatar" id="activeAvatar" aria-hidden="true">EB</div>
                <div><h2 id="activeName">EduBridge Chat</h2><p id="activeSubtitle">Select a person to start a private conversation.</p></div>
                <div class="call-actions hidden" id="callActions"><button type="button" class="call-btn secondary" id="voiceCallButton">☎ Voice</button><button type="button" class="call-btn" id="videoCallButton">▣ Video</button></div>
            </div>
            <div id="welcomeArea" class="welcome-area">
                <div class="welcome-icon" aria-hidden="true">✉</div>
                <h2>Your conversations start here</h2>
                <p>Select a person from the list to open a private chat. Messages are saved to your EduBridge database.</p>
            </div>
            <div id="messages" class="messages hidden" aria-live="polite" aria-label="Conversation messages"></div>
            <div id="notice" class="notice" role="alert"></div>
            <form id="composer" class="composer hidden">
                <input id="messageInput" class="message-input" type="text" maxlength="5000" autocomplete="off" placeholder="Write a message..." aria-label="Write a message" disabled>
                <button id="sendButton" class="send-button" type="submit" disabled>Send</button>
            </form>
        </section>
    </section>
</main>
<div id="incomingCall" class="incoming-call hidden" role="dialog" aria-live="assertive"><strong id="incomingTitle">Incoming call</strong><p id="incomingDescription" class="call-status"></p><div class="call-actions"><button class="call-btn" id="acceptCallButton" type="button">Accept</button><button class="call-btn danger" id="declineCallButton" type="button">Decline</button></div></div>
<div id="callPanel" class="call-panel hidden" role="dialog" aria-label="Call controls"><strong id="callPanelTitle">Call</strong><p id="callStatus" class="call-status">Connecting…</p><div class="call-videos"><video id="remoteVideo" autoplay playsinline></video><video id="localVideo" autoplay muted playsinline></video></div><div class="call-actions"><button class="call-btn secondary" id="muteButton" type="button">Mute mic</button><button class="call-btn secondary" id="cameraButton" type="button">Camera off</button><button class="call-btn danger" id="endCallButton" type="button">End call</button></div></div>
<script>
(() => {
    const apiUrl = new URL('api.php', window.location.href).href;
    const myId = <?= (int)$user['id'] ?>;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const search = document.getElementById('userSearch');
    const userItems = Array.from(document.querySelectorAll('.user-item'));
    const welcome = document.getElementById('welcomeArea');
    const messagesEl = document.getElementById('messages');
    const composer = document.getElementById('composer');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const notice = document.getElementById('notice');
    const activeName = document.getElementById('activeName');
    const activeSubtitle = document.getElementById('activeSubtitle');
    const activeAvatar = document.getElementById('activeAvatar');
    let conversationId = 0;
    let lastMessageId = 0;
    let pollTimer = null;
    let busy = false;

    function showNotice(message) {
        notice.textContent = message;
        notice.classList.add('show');
    }
    function clearNotice() {
        notice.textContent = '';
        notice.classList.remove('show');
    }
    async function request(url, options = {}) {
        const response = await fetch(url, {credentials: 'same-origin', ...options});
        let data;
        try { data = await response.json(); }
        catch (_) { throw new Error('The server returned an unreadable response.'); }
        if (!response.ok || !data.ok) throw new Error(data.error || 'The request failed.');
        return data;
    }
    function initials(first, last) {
        return ((first || '').trim().charAt(0) + (last || '').trim().charAt(0)).toUpperCase() || 'EB';
    }
    function formatTime(value) {
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleString([], {month:'short', day:'numeric', hour:'numeric', minute:'2-digit'});
    }
    function addMessage(message) {
        document.getElementById('emptyConversation')?.remove();
        const mine = Number(message.sender_id) === myId;
        const row = document.createElement('div');
        row.className = 'bubble-row' + (mine ? ' mine' : '');
        const name = document.createElement('div');
        name.className = 'bubble-name';
        name.textContent = mine ? 'You' : `${message.first_name || ''} ${message.last_name || ''}`.trim();
        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.textContent = message.message_text || '';
        const time = document.createElement('div');
        time.className = 'bubble-time';
        time.textContent = formatTime(message.sent_at);
        row.append(name, bubble, time);
        messagesEl.appendChild(row);
    }
    async function loadMessages(initial = false) {
        if (!conversationId || busy) return;
        busy = true;
        try {
            const data = await request(`${apiUrl}?action=messages&conversation_id=${conversationId}&after_id=${initial ? 0 : lastMessageId}`);
            if (initial) {
                messagesEl.replaceChildren();
                lastMessageId = 0;
            }
            if (data.messages.length) {
                data.messages.forEach(message => {
                    addMessage(message);
                    lastMessageId = Math.max(lastMessageId, Number(message.id));
                });
                messagesEl.scrollTop = messagesEl.scrollHeight;
            } else if (initial && lastMessageId === 0) {
                const empty = document.createElement('p');
                empty.className = 'empty-users';
                empty.textContent = 'No messages yet. Send the first message to start the conversation.';
                empty.id = 'emptyConversation';
                messagesEl.appendChild(empty);
            }
        } catch (error) {
            showNotice(error.message);
        } finally {
            busy = false;
        }
    }
    async function openConversation(button) {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = null;
        clearNotice();
        userItems.forEach(item => item.classList.toggle('active', item === button));
        const userId = Number(button.dataset.userId);
        const name = button.querySelector('.user-info strong')?.textContent || 'Conversation';
        const role = button.querySelector('.user-info span')?.textContent || 'EduBridge user';
        callActions.classList.remove('hidden');
        activeName.textContent = name;
        activeSubtitle.textContent = `${role} · Private conversation`;
        activeAvatar.textContent = initials(...name.split(/\s+/, 2));
        welcome.classList.add('hidden');
        messagesEl.classList.remove('hidden');
        messagesEl.replaceChildren();
        composer.classList.remove('hidden');
        input.disabled = true;
        sendButton.disabled = true;
        lastMessageId = 0;
        conversationId = 0;
        try {
            const data = await request(`${apiUrl}?action=start`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({user_id:userId, csrf})
            });
            conversationId = Number(data.conversation_id);
            input.disabled = false;
            sendButton.disabled = false;
            input.focus();
            await loadMessages(true);
            pollTimer = setInterval(() => loadMessages(false), 2500);
        } catch (error) {
            showNotice(error.message);
        }
    }
    userItems.forEach(button => button.addEventListener('click', () => openConversation(button)));
    if (search) {
        search.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            userItems.forEach(item => { item.hidden = !(item.dataset.search || '').includes(query); });
        });
    }
    const callActions = document.getElementById('callActions');
    const voiceCallButton = document.getElementById('voiceCallButton');
    const videoCallButton = document.getElementById('videoCallButton');
    const callPanel = document.getElementById('callPanel');
    const callStatus = document.getElementById('callStatus');
    const callPanelTitle = document.getElementById('callPanelTitle');
    const localVideo = document.getElementById('localVideo');
    const remoteVideo = document.getElementById('remoteVideo');
    const incomingCall = document.getElementById('incomingCall');
    const incomingTitle = document.getElementById('incomingTitle');
    const incomingDescription = document.getElementById('incomingDescription');
    let activeCallId = 0, activeCallType = 'voice', peer = null, localStream = null;
    let signalAfterId = 0, callPollTimer = null, incomingPollTimer = null, incomingCallData = null;
    let makingCall = false, remoteDescriptionSet = false, incomingPollBusy = false;
    const pendingIceCandidates = [];
    const rtcConfig = {iceServers:[{urls:'stun:stun.l.google.com:19302'}]};
    async function callRequest(action, payload = {}) {
        return request(`${apiUrl}?action=${action}`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({...payload,csrf})});
    }
    function setCallStatus(text) { callStatus.textContent = text; }
    async function sendSignal(type, payload = {}) {
        if (!activeCallId) return;
        await callRequest('call-signal', {call_id:activeCallId,signal_type:type,payload});
    }
    async function setupPeer() {
        peer = new RTCPeerConnection(rtcConfig);
        peer.onicecandidate = event => { if (event.candidate && activeCallId) sendSignal('candidate',event.candidate.toJSON()).catch(e=>showNotice(e.message)); };
        peer.ontrack = event => {
            const stream = event.streams && event.streams[0];
            if (!stream) return;
            remoteVideo.srcObject = stream;
            remoteVideo.play().catch(() => setCallStatus('Remote video is ready. Tap the video to start playback if it does not begin automatically.'));
        };
        peer.onconnectionstatechange = () => {
            if (!peer) return;
            if (peer.connectionState === 'connected') setCallStatus('Connected');
            else if (['failed','disconnected'].includes(peer.connectionState)) setCallStatus('Connection interrupted. A TURN server may be required for this network.');
        };
        if (localStream) localStream.getTracks().forEach(track=>peer.addTrack(track,localStream));
    }
    async function acquireMedia(type) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) throw new Error('Camera and microphone require HTTPS on the hosted site, or localhost for local testing.');
        localStream = await navigator.mediaDevices.getUserMedia({audio:true,video:type==='video'});
        localVideo.srcObject = localStream;
        localVideo.classList.toggle('hidden', type !== 'video');
        remoteVideo.classList.toggle('hidden', type !== 'video');
    }
    function showCallPanel(type, title) {
        callPanel.classList.remove('hidden');
        callPanelTitle.textContent = `${type === 'video' ? 'Video' : 'Voice'} call · ${title || 'EduBridge user'}`;
        setCallStatus('Connecting…');
    }
    async function startCall(type) {
        if (!conversationId || activeCallId || makingCall) return;
        makingCall = true; clearNotice();
        try {
            await acquireMedia(type);
            const created = await callRequest('call-start',{conversation_id:conversationId,call_type:type});
            activeCallId = Number(created.call_id); activeCallType = type; signalAfterId = 0; remoteDescriptionSet = false;
            await setupPeer(); showCallPanel(type,activeName.textContent); setCallStatus('Calling… waiting for the other person');
            const offer = await peer.createOffer(); await peer.setLocalDescription(offer);
            await sendSignal('offer',peer.localDescription.toJSON());
            beginCallPolling();
        } catch (error) { showNotice(error.message); await cleanupCall(false); }
        finally { makingCall = false; }
    }
    async function acceptIncoming() {
        if (!incomingCallData) return;
        const item = incomingCallData;
        incomingCallData = null;
        incomingCall.classList.add('hidden');
        try {
            activeCallId = Number(item.call_id); activeCallType = item.call_type; signalAfterId = 0;
            await acquireMedia(activeCallType);
            await callRequest('call-accept',{call_id:activeCallId});
            await setupPeer(); showCallPanel(activeCallType,`${item.first_name} ${item.last_name}`);
            beginCallPolling();
        } catch(error) { showNotice(error.message); await cleanupCall(true,'decline'); }
    }
    async function processSignals(signals) {
        for (const signal of signals) {
            signalAfterId = Math.max(signalAfterId,Number(signal.id));
            if (signal.signal_type === 'offer') {
                if (!peer) await setupPeer();
                await peer.setRemoteDescription(new RTCSessionDescription(signal.payload)); remoteDescriptionSet = true;
                while (pendingIceCandidates.length) {
                    const candidate = pendingIceCandidates.shift();
                    try { await peer.addIceCandidate(new RTCIceCandidate(candidate)); } catch (e) { console.warn('Queued ICE candidate was not accepted', e); }
                }
                const answer = await peer.createAnswer(); await peer.setLocalDescription(answer);
                await sendSignal('answer',peer.localDescription.toJSON()); setCallStatus('Connecting…');
            } else if (signal.signal_type === 'answer' && peer && !peer.currentRemoteDescription) {
                await peer.setRemoteDescription(new RTCSessionDescription(signal.payload)); remoteDescriptionSet = true;
                while (pendingIceCandidates.length) {
                    const candidate = pendingIceCandidates.shift();
                    try { await peer.addIceCandidate(new RTCIceCandidate(candidate)); } catch (e) { console.warn('Queued ICE candidate was not accepted', e); }
                }
            } else if (signal.signal_type === 'candidate' && peer) {
                if (!remoteDescriptionSet) {
                    pendingIceCandidates.push(signal.payload);
                } else {
                    try { await peer.addIceCandidate(new RTCIceCandidate(signal.payload)); } catch(e) { console.warn('ICE candidate was not accepted',e); }
                }
            } else if (signal.signal_type === 'decline') {
                setCallStatus('The other person declined the call.'); await cleanupCall(false); return;
            } else if (signal.signal_type === 'end') {
                setCallStatus('The other person ended the call.'); await cleanupCall(false); return;
            }
        }
    }
    function beginCallPolling() {
        if (callPollTimer) clearInterval(callPollTimer);
        callPollTimer = setInterval(async()=>{
            if (!activeCallId) return;
            try {
                const data = await request(`${apiUrl}?action=call-poll&call_id=${activeCallId}&after_id=${signalAfterId}`);
                if (data.call && ['ended','declined'].includes(data.call.status)) { setCallStatus('Call ended.'); await cleanupCall(false); return; }
                await processSignals(data.signals || []);
            } catch(error) { console.warn('Call polling failed',error); }
        },1000);
    }
    async function cleanupCall(notifyServer=false, signalType='end') {
        const oldId = activeCallId;
        activeCallId = 0;
        incomingCallData = null;
        incomingCall.classList.add('hidden');
        pendingIceCandidates.length = 0;
        remoteDescriptionSet = false;
        if (callPollTimer) clearInterval(callPollTimer); callPollTimer = null;
        if (peer) { peer.onicecandidate=null; peer.ontrack=null; peer.close(); peer=null; }
        if (localStream) localStream.getTracks().forEach(track=>track.stop()); localStream=null;
        localVideo.srcObject=null; remoteVideo.srcObject=null; callPanel.classList.add('hidden');
        if (notifyServer && oldId) { try { await callRequest('call-end',{call_id:oldId,signal_type:signalType}); } catch(e) { showNotice(e.message); } }
    }
    async function pollIncoming() {
        if (activeCallId || incomingCallData || incomingPollBusy || document.hidden) return;
        incomingPollBusy = true;
        try {
            const data = await request(`${apiUrl}?action=call-poll`);
            if (data.incoming && data.incoming.length) {
                incomingCallData = data.incoming[0];
                incomingTitle.textContent = `Incoming ${incomingCallData.call_type} call`;
                incomingDescription.textContent = `${incomingCallData.first_name} ${incomingCallData.last_name} is calling you.`;
                incomingCall.classList.remove('hidden');
            }
        } catch(e) { console.warn('Incoming call check failed',e); }
        finally { incomingPollBusy = false; }
    }
    voiceCallButton.addEventListener('click',()=>startCall('voice'));
    videoCallButton.addEventListener('click',()=>startCall('video'));
    document.getElementById('acceptCallButton').addEventListener('click',acceptIncoming);
    document.getElementById('declineCallButton').addEventListener('click',async()=>{
        if (incomingCallData) { try { await callRequest('call-end',{call_id:Number(incomingCallData.call_id),signal_type:'decline'}); } catch(e) { showNotice(e.message); } }
        incomingCallData=null; incomingCall.classList.add('hidden');
    });
    document.getElementById('endCallButton').addEventListener('click',()=>cleanupCall(true,'end'));
    document.getElementById('muteButton').addEventListener('click',event=>{
        if (!localStream) return;
        const track=localStream.getAudioTracks()[0]; if (!track) return; track.enabled=!track.enabled;
        event.currentTarget.textContent=track.enabled?'Mute mic':'Unmute mic';
    });
    document.getElementById('cameraButton').addEventListener('click',event=>{
        if (!localStream) return;
        const track=localStream.getVideoTracks()[0]; if (!track) { setCallStatus('This is a voice-only call.'); return; }
        track.enabled=!track.enabled; event.currentTarget.textContent=track.enabled?'Camera off':'Camera on';
    });
    pollIncoming();
    incomingPollTimer = setInterval(pollIncoming,1500);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) pollIncoming(); });

    composer.addEventListener('submit', async event => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || !conversationId || sendButton.disabled) return;
        clearNotice();
        sendButton.disabled = true;
        try {
            await request(`${apiUrl}?action=send`, {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({conversation_id:conversationId, message, csrf})
            });
            input.value = '';
            await loadMessages(false);
            input.focus();
        } catch (error) {
            showNotice(error.message);
        } finally {
            sendButton.disabled = false;
        }
    });
})();
</script>
</body>
</html>
