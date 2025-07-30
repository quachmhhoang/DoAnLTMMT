// Authentication Forms JavaScript

// Password toggle functionality
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Remove loading state after 5 seconds (fallback)
            setTimeout(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }, 5000);
        });
    }

    // Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirm_password');
        
        // Password confirmation validation
        function validatePasswordConfirm() {
            const password = passwordField.value;
            const confirmPassword = confirmPasswordField.value;
            
            if (confirmPassword && password !== confirmPassword) {
                confirmPasswordField.classList.add('is-invalid');
                confirmPasswordField.classList.remove('is-valid');
                
                // Add or update invalid feedback
                let feedback = confirmPasswordField.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.classList.add('invalid-feedback');
                    confirmPasswordField.parentNode.appendChild(feedback);
                }
                feedback.textContent = 'Mật khẩu xác nhận không khớp';
                
                return false;
            } else if (confirmPassword) {
                confirmPasswordField.classList.remove('is-invalid');
                confirmPasswordField.classList.add('is-valid');
                
                // Remove invalid feedback
                const feedback = confirmPasswordField.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.remove();
                }
                
                return true;
            }
            
            return true;
        }
        
        // Real-time password validation
        confirmPasswordField.addEventListener('input', validatePasswordConfirm);
        passwordField.addEventListener('input', function() {
            if (confirmPasswordField.value) {
                validatePasswordConfirm();
            }
        });
        
        // Email validation
        const emailField = document.getElementById('email');
        emailField.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                
                let feedback = this.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.classList.add('invalid-feedback');
                    this.parentNode.appendChild(feedback);
                }
                feedback.textContent = 'Email không hợp lệ';
            } else if (email) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                
                const feedback = this.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.remove();
                }
            }
        });
        
        // Phone validation
        const phoneField = document.getElementById('phone');
        phoneField.addEventListener('blur', function() {
            const phone = this.value;
            const phoneRegex = /^[0-9]{10,11}$/;
            
            if (phone && !phoneRegex.test(phone)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                
                let feedback = this.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.classList.add('invalid-feedback');
                    this.parentNode.appendChild(feedback);
                }
                feedback.textContent = 'Số điện thoại phải có 10-11 chữ số';
            } else if (phone) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                
                const feedback = this.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.remove();
                }
            }
        });
        
        // Form submission
        registerForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Validate password confirmation before submit
            if (!validatePasswordConfirm()) {
                e.preventDefault();
                confirmPasswordField.focus();
                return;
            }
            
            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Remove loading state after 5 seconds (fallback)
            setTimeout(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }, 5000);
        });
    }
    
    // Add floating animation to form fields
    const formControls = document.querySelectorAll('.auth-card .form-control');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentNode.style.transform = 'translateY(-2px)';
        });
        
        control.addEventListener('blur', function() {
            this.parentNode.style.transform = 'translateY(0)';
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
});

// Add smooth transitions for form interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to form groups
    const formGroups = document.querySelectorAll('.auth-card .mb-3');
    formGroups.forEach(group => {
        group.style.transition = 'transform 0.2s ease';
        
        const input = group.querySelector('.form-control');
        if (input) {
            input.addEventListener('focus', () => {
                group.style.transform = 'translateX(5px)';
            });
            
            input.addEventListener('blur', () => {
                group.style.transform = 'translateX(0)';
            });
        }
    });
});
