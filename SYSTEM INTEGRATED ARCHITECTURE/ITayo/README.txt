EduBridgeAcademy WebRTC calling update

Files:
- chat/index.php: updated chat interface with voice/video call controls
- chat/api.php: updated API with WebRTC signaling endpoints
- chat/webrtc_signaling_migration.sql: creates chat_call_signals table

Before applying:
1. Back up the whole project and database.
2. Import webrtc_signaling_migration.sql in phpMyAdmin into the edubridgeacademy database.
3. Replace C:\xampp1\htdocs\EdubridgeAcademy\chat\index.php and api.php with these files.
4. Test on localhost with two different active accounts.
5. For hosted testing, use HTTPS. This build uses Google's public STUN server. For reliable connections across restrictive networks, configure a TURN server and add its URL and credentials to rtcConfig in chat/index.php.

Important:
- Existing text-message actions are retained.
- Call media is peer-to-peer; the PHP API relays signaling messages.
- WebRTC calls have not been end-to-end tested in the user's environment.
- Check that chat_call_sessions.status supports the values 'ringing', 'accepted', 'declined', and 'ended'. If your actual ENUM differs, adjust the enum/migration before testing; do not alter existing rows blindly.
