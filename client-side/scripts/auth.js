const API_URL = 'http://localhost/Chatting_app/server-side/index.php';

if (localStorage.getItem('chat_user')) {
    window.location.href = 'chat.html';
}

function handleLogin() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('error-msg');

    if (!email || !password) {
        errorDiv.innerText = "Please fill all fields";
        errorDiv.classList.remove('hide');
        return;
    }

    const params = new URLSearchParams();
    params.append('email', email);
    params.append('password', password);

    axios.post(API_URL + '/login', params)
        .then(res => {
            if (res.data.status === 'success') {
                localStorage.setItem('chat_user', JSON.stringify(res.data.data));
                window.location.href = 'chat.html';
            } else {
                errorDiv.innerText = res.data.message;
                errorDiv.classList.remove('hide');
            }
        })
        .catch(err => {
            errorDiv.innerText = "Connection Error";
            errorDiv.classList.remove('hide');
        });
}

function handleRegister() {
    const name = document.getElementById('full_name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('error-msg');

    if (!name || !email || !password) {
        errorDiv.innerText = "Please fill all fields";
        errorDiv.classList.remove('hide');
        return;
    }

    const params = new URLSearchParams();
    params.append('full_name', name);
    params.append('email', email);
    params.append('password', password);

    axios.post(API_URL + '/register', params)
        .then(res => {
            if (res.data.status === 'success') {
                alert('Registration successful! Please login.');
                window.location.href = 'login.html';
            } else {
                errorDiv.innerText = res.data.message;
                errorDiv.classList.remove('hide');
            }
        })
        .catch(err => {
            errorDiv.innerText = "Connection Error";
            errorDiv.classList.remove('hide');
        });
}