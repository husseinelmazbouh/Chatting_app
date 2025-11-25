
const API_URL = 'http://localhost/Chatting_app/server-side/index.php';

const storedUser = localStorage.getItem('chat_user');
if (!storedUser) {
    window.location.href = '../pages/login.html';
}
const currentUser = JSON.parse(storedUser);

let currentChatId = null;
let currentTargetId = null;
let pollInterval = null;

if(document.getElementById('user-display')) {
    document.getElementById('user-display').innerText = currentUser.full_name;
}

loadContacts();

pollInterval = setInterval(function() {
    if (currentChatId) {
        loadMessages(true);
        markAsRead(); 
    }
}, 2000);

function loadContacts() {
    axios.get(API_URL + "?route=/users&user_id=" + currentUser.user_id)
        .then(res => {
            const list = document.getElementById('users-list');
            if(!list) return;
            
            list.innerHTML = '';
            
            let users = [];
            if (res.data.data && Array.isArray(res.data.data.data)) {
                users = res.data.data.data;
            } else if (Array.isArray(res.data.data)) {
                users = res.data.data;
            } else if (res.data.data && typeof res.data.data === 'object') {
                if(res.data.data.data && Array.isArray(res.data.data.data)) {
                        users = res.data.data.data;
                } else {
                        users = Object.values(res.data.data).filter(item => typeof item === 'object');
                }
            }
            
            if (!Array.isArray(users)) {
                console.error("Users API response is not an array:", res.data);
                list.innerHTML = `<div style="padding:20px;text-align:center;color:red;">API Error: Invalid data format</div>`;
                return;
            }

            if (users.length === 0) {
                list.innerHTML = `<div style="padding:20px;text-align:center;color:#666;">No contacts found.<br>Register a second user to test!</div>`;
                return;
            }
            users.forEach(u => {
                if(u.id == currentUser.user_id) return;
                const div = document.createElement('div');
                div.className = 'user-row';
                div.innerHTML = `${u.full_name}`;
                
                div.onclick = function() {
                    document.querySelectorAll('.user-row').forEach(r => r.classList.remove('active'));
                    div.classList.add('active');
                    startChat(u.id, u.full_name);
                };
                
                if (currentTargetId === u.id) div.classList.add('active');
                list.appendChild(div);
            });
        })
        .catch(err => {
            console.error("Error loading contacts", err);
            const list = document.getElementById('users-list');
            if(list) list.innerHTML = `<div style="padding:20px;text-align:center;color:red;">Connection Failed</div>`;
        });
}

function startChat(targetId, name) {
    currentTargetId = targetId;
    document.getElementById('chat-with-name').innerText = name;
    
    document.getElementById('chat-header').classList.remove('hide');
    document.getElementById('input-area').classList.remove('hide');
    document.getElementById('placeholder-text').classList.add('hide');
    document.getElementById('ai-result').classList.add('hide');

    const area = document.getElementById('messages-area');
    area.innerHTML = '<p class="p-holder">Loading...</p>';

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('target_user_id', targetId);

    axios.post(API_URL + "?route=/chat/start", params)
        .then(res => {
            currentChatId = res.data.conversation_id;
            if(!currentChatId && res.data.data && res.data.data.conversation_id) {
                currentChatId = res.data.data.conversation_id;
            }
            loadMessages(false);
            markAsRead();
        })
        .catch(err => console.error("Error starting chat", err));
}

function loadMessages(isPolling) {
    if (!currentChatId) return;

    axios.get(API_URL + "?route=/chat/history&user_id=" + currentUser.user_id + "&conversation_id=" + currentChatId)
        .then(res => {
            const area = document.getElementById('messages-area');
            if(!area) return;

            let messages = [];
            if(res.data.data && Array.isArray(res.data.data.data)) {
                    messages = res.data.data.data;
            } else if (Array.isArray(res.data.data)) {
                    messages = res.data.data;
            }

            const isAtBottom = area.scrollHeight - area.scrollTop - area.clientHeight < 100;
            
            if (!isPolling) area.innerHTML = ''; 
            if (isPolling) area.innerHTML = ''; 

            if (messages.length === 0) {
                area.innerHTML = '<p class="p-holder">No messages yet. Say Hi!</p>';
                return;
            }

            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'message-bubble ' + (msg.is_mine ? 'mine' : 'theirs');
                
                // sexy features
                let ticks = '';
                if (msg.is_mine) {
                    if (msg.status_text === 'read') {
                        ticks = '<span class="tick-span tick-read">✓✓</span>';
                    } else if (msg.status_text === 'delivered') {
                        ticks = '<span class="tick-span tick-delivered">✓✓</span>';
                    } else {
                        ticks = '<span class="tick-span tick-sent">✓</span>';
                    }
                }

                //time formating
                let timeStr = "";
                try {
                    const date = new Date(msg.created_at);
                    timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                } catch(e) {}
                
                div.innerHTML = `
                    ${msg.message}
                    <div style="font-size:10px; color:rgba(0,0,0,0.5); text-align:right; margin-top:2px;">
                        ${timeStr} ${ticks}
                    </div>
                `;
                area.appendChild(div);
            });

            if (!isPolling || isAtBottom) {
                area.scrollTop = area.scrollHeight;
            }
        })
        .catch(err => console.error("Error loading messages", err));
}

function sendMessage() {
    const input = document.getElementById('msg-input');
    const text = input.value.trim();
    if (!text || !currentChatId) return;

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('conversation_id', currentChatId);
    params.append('message', text);
    
    input.value = ''; 

    axios.post(API_URL + "?route=/chat/send", params)
        .then(() => {
            loadMessages(false); 
        })
        .catch(err => alert("Error sending message"));
}

function handleEnter(event) {
    if (event.key === 'Enter') sendMessage();
}

function markAsRead() {
    if(!currentChatId) return;
    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('conversation_id', currentChatId);
    axios.post(API_URL + "?route=/chat/read", params);
}

function getSummary() {
    const box = document.getElementById('ai-result');
    box.classList.remove('hide');
    box.innerHTML = ' <b>Generating AI Summary...</b>';

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('conversation_id', currentChatId);

    axios.post(API_URL + "?route=/ai/summary", params)
        .then(res => {
            let text = "";
            
            const d = res.data;
            if (d.summary) text = d.summary;
            else if (d.data && d.data.summary) text = d.data.summary;
            else if (d.error) text = d.error;
            else if (d.data && d.data.error) text = d.data.error;
            else text = "No summary available.";
            
            box.innerHTML = `<strong> AI Summary:</strong><br>${text}`;
        })
        .catch(err => {
            box.innerHTML = " Error connecting to AI service.";
        });
}

function logout() {
    localStorage.removeItem('chat_user');
    window.location.href = '../pages/login.html';
}