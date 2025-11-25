const BASE_URL = 'http://localhost/Chatting_app/server-side/index.php';

if (localStorage.getItem('chat_user') && !window.location.href.includes('chat.html')) {
    window.location.href = '../pages/chat.html';
}

function handleLogin() {
    const emailInput = document.getElementById('email');
    const passInput = document.getElementById('password');
    const btn = document.querySelector('button');
    
    const email = emailInput ? emailInput.value.trim() : '';
    const password = passInput ? passInput.value.trim() : '';

    if (!email || !password) {
        showError("Please fill all fields");
        return;
    }

    if(btn) { 
        btn.disabled = true; 
        btn.innerText = "Logging in...";
    }

    const params = new URLSearchParams();
    params.append('email', email);
    params.append('password', password);

    console.log("Attempting Login to:", BASE_URL);

    axios.post(BASE_URL + '?route=/login', params)
        .then(res => {
            console.log("Login Response:", res.data);

            if (res.data.status === 200 || res.data.status === 'success') {
                let userData;
                
                if (res.data.data && res.data.data.data) {
                    userData = res.data.data.data;
                } else {
                    userData = res.data.data;
                }
                
                localStorage.setItem('chat_user', JSON.stringify(userData));
                window.location.replace('chat.html'); 
            } else {
                const msg = res.data.data || res.data.message || "Login failed";
                showError(typeof msg === 'string' ? msg : JSON.stringify(msg));
                if(btn) { btn.disabled = false; btn.innerText = "Login"; }
            }
        })
        .catch(err => {
            console.error(err);
            showError("Connection Error or Invalid URL");
            if(btn) { btn.disabled = false; btn.innerText = "Login"; }
        });
}

function handleRegister() {
    const nameInput = document.getElementById('full_name');
    const emailInput = document.getElementById('email');
    const passInput = document.getElementById('password');
    const btn = document.querySelector('button');

    const name = nameInput ? nameInput.value.trim() : '';
    const email = emailInput ? emailInput.value.trim() : '';
    const password = passInput ? passInput.value.trim() : '';

    if (!name || !email || !password) {
        showError("Please fill all fields");
        return;
    }

    if(btn) { btn.disabled = true; btn.innerText = "Processing..."; }

    const params = new URLSearchParams();
    params.append('full_name', name);
    params.append('email', email);
    params.append('password', password);

    console.log("Attempting Register to:", BASE_URL);

    axios.post(BASE_URL + '?route=/register', params)
        .then(res => {
            if (res.data.status === 200 || res.data.status === 'success') {
                alert('Registration successful! Please login.');
                window.location.href = '../pages/login.html';
            } else {
                const msg = res.data.data || res.data.message;
                showError(typeof msg === 'string' ? msg : JSON.stringify(msg));
                if(btn) { btn.disabled = false; btn.innerText = "Register"; }
            }
        })
        .catch(err => {
            console.error(err);
            showError("Connection Error or Invalid URL");
            if(btn) { btn.disabled = false; btn.innerText = "Register"; }
        });
}

function showError(msg) {
    const el = document.getElementById('error-msg');
    if (el) {
        el.innerText = msg;
        el.classList.remove('hide');
    } else {
        alert(msg);
    }
}