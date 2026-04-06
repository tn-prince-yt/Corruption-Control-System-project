/**
 * Form Validation JavaScript
 * Handles client-side form validation
 */

// Validate email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate password
function validatePassword(password) {
    // At least 8 characters, one uppercase, one lowercase, one digit, one special character
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return re.test(password);
}

// Validate phone
function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone);
}

// Validate required field
function validateRequired(value) {
    return value && value.trim() !== '';
}

// Validate file upload
function validateFileUpload(fileInput, maxSizeMB = 5) {
    if (!fileInput.files || fileInput.files.length === 0) {
        return { valid: false, message: 'Please select a file' };
    }
    
    const file = fileInput.files[0];
    const maxSizeBytes = maxSizeMB * 1024 * 1024;
    
    if (file.size > maxSizeBytes) {
        return { valid: false, message: `File size exceeds ${maxSizeMB}MB` };
    }
    
    const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!allowedExtensions.includes(fileExtension)) {
        return { valid: false, message: 'File type not allowed' };
    }
    
    return { valid: true, message: 'File is valid' };
}

// Display validation error
function displayError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#e74c3c';
        field.style.backgroundColor = '#ffe6e6';
        
        let errorElement = field.nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('error-message')) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }
        errorElement.textContent = message;
        errorElement.style.color = '#e74c3c';
        errorElement.style.fontSize = '0.9rem';
        errorElement.style.marginTop = '0.25rem';
    }
}

// Clear validation error
function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#ddd';
        field.style.backgroundColor = '';
        
        const errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error-message')) {
            errorElement.remove();
        }
    }
}

// Clear all errors
function clearAllErrors(formId) {
    const form = document.getElementById(formId);
    if (form) {
        const fields = form.querySelectorAll('input, textarea, select');
        fields.forEach(field => {
            clearError(field.id);
        });
    }
}

// Validate login form
function validateLoginForm() {
    clearAllErrors('loginForm');
    let isValid = true;
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!validateRequired(email)) {
        displayError('email', 'Email is required');
        isValid = false;
    } else if (!validateEmail(email)) {
        displayError('email', 'Please enter a valid email');
        isValid = false;
    }
    
    if (!validateRequired(password)) {
        displayError('password', 'Password is required');
        isValid = false;
    }
    
    return isValid;
}

// Validate register form
function validateRegisterForm() {
    clearAllErrors('registerForm');
    let isValid = true;
    
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const phone = document.getElementById('phone').value;
    const role = document.getElementById('role').value;
    
    if (!validateRequired(name)) {
        displayError('name', 'Name is required');
        isValid = false;
    }
    
    if (!validateRequired(email)) {
        displayError('email', 'Email is required');
        isValid = false;
    } else if (!validateEmail(email)) {
        displayError('email', 'Please enter a valid email');
        isValid = false;
    }
    
    if (!validateRequired(password)) {
        displayError('password', 'Password is required');
        isValid = false;
    } else if (!validatePassword(password)) {
        displayError('password', 'Password must be at least 8 characters with uppercase, lowercase, digit, and special character');
        isValid = false;
    }
    
    if (password !== confirmPassword) {
        displayError('confirm_password', 'Passwords do not match');
        isValid = false;
    }
    
    if (!validateRequired(phone)) {
        displayError('phone', 'Phone number is required');
        isValid = false;
    } else if (!validatePhone(phone)) {
        displayError('phone', 'Please enter a valid 10-digit phone number');
        isValid = false;
    }
    
    if (!validateRequired(role)) {
        displayError('role', 'Please select a role');
        isValid = false;
    }
    
    return isValid;
}

// Validate complaint form
function validateComplaintForm() {
    clearAllErrors('complaintForm');
    let isValid = true;
    
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const category = document.getElementById('category').value;
    const location = document.getElementById('location').value;
    
    if (!validateRequired(title)) {
        displayError('title', 'Complaint title is required');
        isValid = false;
    }
    
    if (!validateRequired(description)) {
        displayError('description', 'Description is required');
        isValid = false;
    } else if (description.length < 50) {
        displayError('description', 'Description must be at least 50 characters');
        isValid = false;
    }
    
    if (!validateRequired(category)) {
        displayError('category', 'Please select a category');
        isValid = false;
    }
    
    if (!validateRequired(location)) {
        displayError('location', 'Location is required');
        isValid = false;
    }
    
    return isValid;
}

// Validate evidence upload form
function validateEvidenceForm() {
    const fileInput = document.getElementById('evidence_file');
    const validation = validateFileUpload(fileInput);
    
    if (!validation.valid) {
        displayError('evidence_file', validation.message);
        return false;
    }
    
    return true;
}

// Add real-time validation to fields
document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                displayError(this.id, 'Please enter a valid email');
            } else {
                clearError(this.id);
            }
        });
    });
    
    // Password validation
    const passwordFields = document.querySelectorAll('input[name="password"]');
    passwordFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value && this.id && this.id.includes('register')) {
                if (!validatePassword(this.value)) {
                    displayError(this.id, 'Password must be at least 8 characters with uppercase, lowercase, digit, and special character');
                } else {
                    clearError(this.id);
                }
            }
        });
    });
    
    // Phone validation
    const phoneFields = document.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value && !validatePhone(this.value)) {
                displayError(this.id, 'Please enter a valid 10-digit phone number');
            } else {
                clearError(this.id);
            }
        });
    });
});
