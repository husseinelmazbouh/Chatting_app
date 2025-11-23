const API_URL = 'http://localhost/Chatting_app/server-side/index.php';

const storedUser = localStorage.getItem('chat_user');
if (!storedUser) {
    window.location.href = 'login.html';
}

const currentUser = JSON.parse(storedUser);
let currentChatId = null;
let currentTargetId = null;
let pollInterval = null;


document.getElementById('user-display').innerText = "Welcome, " + currentUser.full_name;
loadContacts();

// Poll for new messages every 3 seconds
pollInterval = setInterval(function() {
    if (currentChatId) {
        loadMessages(true);
    }
}, 3000);

function loadContacts() {
    axios.get(API_URL + "?route=/users&user_id=" + currentUser.user_id)
        .then(res => {
            const list = document.getElementById('users-list');
            list.innerHTML = '';
            res.data.data.forEach(u => {
                const div = document.createElement('div');
                div.className = 'user-row';
                div.innerText = u.full_name;
                
                div.onclick = function() {
                    startChat(u.id, u.full_name);
                };
                
                if (currentTargetId === u.id) {
                    div.classList.add('active');
                }
                
                list.appendChild(div);
            });
        });
}

function startChat(targetId, name) {
    currentTargetId = targetId;
    document.getElementById('chat-with-name').innerText = name;
    
    document.getElementById('chat-header').classList.remove('hide');
    document.getElementById('input-area').classList.remove('hide');
    document.getElementById('placeholder-text').classList.add('hide');
    document.getElementById('ai-result').classList.add('hide');

    loadContacts(); 

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('target_user_id', targetId);

    axios.post(API_URL + "?route=/chat/start", params)
        .then(res => {
            currentChatId = res.data.conversation_id;
            loadMessages(false);
            markAsRead();
        });
}

function loadMessages(isPolling) {
    if (!currentChatId) return;

    axios.get(API_URL + "?route=/chat/history&user_id=" + currentUser.user_id + "&conversation_id=" + currentChatId)
        .then(res => {
            const area = document.getElementById('messages-area');
            const isAtBottom = area.scrollHeight - area.scrollTop - area.clientHeight < 100;

            if (!isPolling) area.innerHTML = ''; 
            if (isPolling) area.innerHTML = ''; 

            res.data.data.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('message-bubble');
                if (msg.is_mine) {
                    div.classList.add('mine');
                } else {
                    div.classList.add('theirs');
                }
                
                let ticks = '';
                if (msg.is_mine) {
                    if (msg.status_text === 'read') ticks = '<span class="tick-span tick-read">✓✓</span>';
                    else if (msg.status_text === 'delivered') ticks = '<span class="tick-span tick-delivered">✓✓</span>';
                    else ticks = '<span class="tick-span tick-sent">✓</span>';
                }

                div.innerHTML = msg.message + " " + ticks;
                area.appendChild(div);
            });

            if (!isPolling || isAtBottom) {
                area.scrollTop = area.scrollHeight;
            }
        });
}

function sendMessage() {
    const input = document.getElementById('msg-input');
    const text = input.value.trim();
    if (!text) return;

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('conversation_id', currentChatId);
    params.append('message', text);

    axios.post(API_URL + "?route=/chat/send", params)
        .then(() => {
            input.value = '';
            loadMessages(false);
        });
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
    box.innerText = 'Analyzing conversation...';

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('conversation_id', currentChatId);

    axios.post(API_URL + "?route=/ai/summary", params)
        .then(res => {
            if (res.data.summary) {
                box.innerText = res.data.summary;
            } else if (res.data.error) {
                box.innerText = res.data.error;
            } else {
                box.innerText = "No response from AI.";
            }
        });
}

function logout() {
    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    axios.post(API_URL + "?route=/logout", params).then(() => {
        localStorage.removeItem('chat_user');
        window.location.href = 'login.html';
    });
}