// Toggle between login and signup forms
document.addEventListener('DOMContentLoaded', function() {
    const loginButton = document.getElementById('loginbutton');
    const signupButton = document.getElementById('signupbutton');
    const signupForm = document.getElementById('signup');
    const loginForm = document.getElementById('login');

    // Clear all form inputs on page load
    const allInputs = document.querySelectorAll('input');
    allInputs.forEach(input => {
        input.value = '';
        input.setAttribute('autocomplete', 'off');
        
        // Add input event listener to handle label visibility
        input.addEventListener('input', function() {
            const label = this.nextElementSibling;
            if (label && label.tagName === 'LABEL') {
                if (this.value !== '') {
                    label.style.opacity = '0';
                    label.style.visibility = 'hidden';
                } else {
                    label.style.opacity = '1';
                    label.style.visibility = 'visible';
                }
            }
        });
        
        // Handle focus events
        input.addEventListener('focus', function() {
            const label = this.nextElementSibling;
            if (label && label.tagName === 'LABEL') {
                label.style.opacity = '0';
                label.style.visibility = 'hidden';
            }
        });
        
        // Handle blur events
        input.addEventListener('blur', function() {
            const label = this.nextElementSibling;
            if (label && label.tagName === 'LABEL' && this.value === '') {
                label.style.opacity = '1';
                label.style.visibility = 'visible';
            }
        });
    });

    // Show login form by default
    if (loginForm) {
        loginForm.style.display = 'block';
    }
    if (signupForm) {
        signupForm.style.display = 'none';
    }

    // Switch to login form
    if (loginButton) {
        loginButton.addEventListener('click', function() {
            signupForm.style.display = 'none';
            loginForm.style.display = 'block';
            // Clear login form
            const loginInputs = loginForm.querySelectorAll('input');
            loginInputs.forEach(input => {
                input.value = '';
                const label = input.nextElementSibling;
                if (label && label.tagName === 'LABEL') {
                    label.style.opacity = '1';
                    label.style.visibility = 'visible';
                }
            });
        });
    }

    // Switch to signup form
    if (signupButton) {
        signupButton.addEventListener('click', function() {
            loginForm.style.display = 'none';
            signupForm.style.display = 'block';
            // Clear signup form
            const signupInputs = signupForm.querySelectorAll('input');
            signupInputs.forEach(input => {
                input.value = '';
                const label = input.nextElementSibling;
                if (label && label.tagName === 'LABEL') {
                    label.style.opacity = '1';
                    label.style.visibility = 'visible';
                }
            });
        });
    }
});
