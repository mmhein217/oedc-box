// Function to toggle between login and signup forms
function toggleForm(formType) {
    if (formType === 'signup') {
        document.getElementById('login-form').classList.remove('active');
        document.getElementById('signup-form').classList.add('active');
    } else {
        document.getElementById('signup-form').classList.remove('active');
        document.getElementById('login-form').classList.add('active');
    }
}

// Function to handle login logic
function login() {
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;

    // Implement your login logic here (e.g., call API, etc.)
    alert(`Logged in with username: ${username} and password: ${password}`);
}

// Function to handle signup logic
function signup() {
    const username = document.getElementById('signup-username').value;
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('signup-confirm-password').value;

    if (password !== confirmPassword) {
        alert("Passwords don't match!");
        return;
    }

    // Implement your signup logic here (e.g., call API, etc.)
    alert(`Signed up with username: ${username}`);
}
