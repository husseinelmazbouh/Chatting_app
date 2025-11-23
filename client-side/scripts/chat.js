const API_URL = 'http://localhost/Chatting_app/server-side/index.php';

const storedUser = localStorage.getItem('chat_user');
if (!storedUser) {
    window.location.href = 'login.html';
}

const currentUser = JSON.parse(storedUser);
let currentChatId = null;
let currentTargetId = null;
let pollInterval = null;

document.getElementById('user-display').innerText = `Welcome, ${currentUser.full_name}`;
loadUsers();

pollInterval = setInterval(() => {
    if (currentChatId) loadMessages(true);
}, 3000);

function loadUsers() {
    axios.get(`${API_URL}/users?user_id=${currentUser.user_id}`)
        .then(res => {
            const list = document.getElementById('users-list');
            list.innerHTML = '';
            res.data.data.forEach(u => {
                const div = document.createElement('div');
                div.className = 'user-row';
                div.innerText = u.full_name;
                div.onclick = () => openChat(u.id, u.full_name);
                if (currentTargetId === u.id) div.classList.add('active');
                list.appendChild(div);
            });
        });
}

function openChat(targetId, name) {
    currentTargetId = targetId;
    document.getElementById('chat-with-name').innerText = name;
    
    // Toggle CSS classes instead of style.display
    document.getElementById('chat-header').classList.remove('hide');
    document.getElementById('input-area').classList.remove('hide');
    document.getElementById('ai-result').classList.add('hide');
    document.getElementById('placeholder-text').classList.add('hide');
    
    loadUsers(); 

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('target_user_id', targetId);

    axios.post(`${API_URL}/chat/start`, params)
        .then(res => {
            currentChatId = res.data.conversation_id;
            loadMessages(false);
            
            const readParams = new URLSearchParams();
            readParams.append('user_id', currentUser.user_id);
            readParams.append('conversation_id', currentChatId);
            axios.post(`${API_URL}/chat/read`, readParams);
        });
}

function loadMessages(isPolling) {
    if (!currentChatId) return;

    axios.get(`${API_URL}/chat/history?user_id=${currentUser.user_id}&conversation_id=${currentChatId}`)
        .then(res => {
            const area = document.getElementById('messages-area');
            if (!isPolling) area.innerHTML = ''; 
            
            const oldScroll = area.scrollTop;
            const isNearBottom = area.scrollHeight - area.scrollTop - area.clientHeight < 100;

            if (isPolling) area.innerHTML = ''; 

            res.data.data.forEach(msg => {
                const div = document.createElement('div');
                div.className = `message-bubble ${msg.is_mine ? 'mine' : 'theirs'}`;
                
                let ticks = '';
                if (msg.is_mine) {
                    if (msg.status_text === 'read') ticks = '<span class="tick-span tick-read">!!</span>';
                    else if (msg.status_text === 'delivered') ticks = '<span class="tick-span tick-delivered">!!</span>';
                    else ticks = '<span class="tick-span tick-sent">!</span>';
                }

                div.innerHTML = `${msg.message} ${ticks}`;
                area.appendChild(div);
            });

            if (!isPolling || isNearBottom) {
                area.scrollTop = area.scrollHeight;
            } else if (isPolling) {
                area.scrollTop = oldScroll;
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

    axios.post(`${API_URL}/chat/send`, params)
        .then(res => {
            input.value = '';
            loadMessages(false);
        });
}

function getSummary() {
    const box = document.getElementById('ai-result');
    box.classList.remove('hide');
    box.innerText = 'Loading AI summary...';

    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    params.append('conversation_id', currentChatId);

    axios.post(`${API_URL}/ai/summary`, params)
        .then(res => {
            if (res.data.summary) {
                box.innerText = res.data.summary;
            } else if (res.data.error) {
                box.innerText = res.data.error;
            }
        });
}

function logout() {
    const params = new URLSearchParams();
    params.append('user_id', currentUser.user_id);
    axios.post(`${API_URL}/logout`, params).then(() => {
        localStorage.removeItem('chat_user');
        window.location.href = 'login.html';
    });
}