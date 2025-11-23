const BASE_URL = 'http://localhost/Chatting_app/server-side/index.php';

if (localStorage.getItem('chat_user')) {
    window.location.href = '../pages/chat.html';
}

function handleLogin() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('error-msg');

    if (!email || !password) {
        showError("Please fill all fields");
        return;
    }

    const params = new URLSearchParams();
    params.append('email', email);
    params.append('password', password);

    axios.post(BASE_URL + '?route=/login', params)
        .then(res => {
            if (res.data.status === 'success') {
                localStorage.setItem('chat_user', JSON.stringify(res.data.data));
                window.location.href = '../pages/chat.html';
            } else {
                showError(res.data.message);
            }
        })
        .catch(err => {
            console.error(err);
            showError("Connection Error or Invalid Credentials");
        });
}

function handleRegister() {
    const name = document.getElementById('full_name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!name || !email || !password) {
        showError("Please fill all fields");
        return;
    }

    const params = new URLSearchParams();
    params.append('full_name', name);
    params.append('email', email);
    params.append('password', password);

    axios.post(BASE_URL + '?route=/register', params)
        .then(res => {
            if (res.data.status === 'success') {
                alert('Registration successful! Please login.');
                window.location.href = '../pages/login.html';
            } else {
                showError(res.data.message);
            }
        })
        .catch(err => showError("Connection Error"));
}

function showError(msg) {
    const el = document.getElementById('error-msg');
    el.innerText = msg;
    el.classList.remove('hide');
}