/**
 * Form Validation Utility
 */

export class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        this.errors = {};
    }

    /**
     * Validate required fields
     */
    validateRequired(fieldName, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return true;

        const value = field.value.trim();
        if (!value) {
            this.addError(fieldName, message || `${this.getFieldLabel(field)} is required`);
            return false;
        }
        return true;
    }

    /**
     * Validate email format
     */
    validateEmail(fieldName, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            this.addError(fieldName, message || 'Please enter a valid email address');
            return false;
        }
        return true;
    }

    /**
     * Validate phone number
     */
    validatePhone(fieldName, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        const phoneRegex = /^[0-9]{10}$/;
        if (!phoneRegex.test(field.value.replace(/[\s\-\(\)]/g, ''))) {
            this.addError(fieldName, message || 'Please enter a valid 10-digit phone number');
            return false;
        }
        return true;
    }

    /**
     * Validate minimum length
     */
    validateMinLength(fieldName, minLength, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        if (field.value.length < minLength) {
            this.addError(fieldName, message || `${this.getFieldLabel(field)} must be at least ${minLength} characters`);
            return false;
        }
        return true;
    }

    /**
     * Validate maximum length
     */
    validateMaxLength(fieldName, maxLength, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        if (field.value.length > maxLength) {
            this.addError(fieldName, message || `${this.getFieldLabel(field)} must not exceed ${maxLength} characters`);
            return false;
        }
        return true;
    }

    /**
     * Validate numeric value
     */
    validateNumeric(fieldName, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        if (isNaN(field.value)) {
            this.addError(fieldName, message || `${this.getFieldLabel(field)} must be a number`);
            return false;
        }
        return true;
    }

    /**
     * Validate minimum value
     */
    validateMin(fieldName, minValue, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        if (parseFloat(field.value) < minValue) {
            this.addError(fieldName, message || `${this.getFieldLabel(field)} must be at least ${minValue}`);
            return false;
        }
        return true;
    }

    /**
     * Validate maximum value
     */
    validateMax(fieldName, maxValue, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        if (parseFloat(field.value) > maxValue) {
            this.addError(fieldName, message || `${this.getFieldLabel(field)} must not exceed ${maxValue}`);
            return false;
        }
        return true;
    }

    /**
     * Validate date format
     */
    validateDate(fieldName, message = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) return true;

        const date = new Date(field.value);
        if (isNaN(date.getTime())) {
            this.addError(fieldName, message || 'Please enter a valid date');
            return false;
        }
        return true;
    }

    /**
     * Validate password match
     */
    validatePasswordMatch(passwordField, confirmField, message = null) {
        const password = this.form.querySelector(`[name="${passwordField}"]`);
        const confirm = this.form.querySelector(`[name="${confirmField}"]`);
        
        if (!password || !confirm || !password.value || !confirm.value) return true;

        if (password.value !== confirm.value) {
            this.addError(confirmField, message || 'Passwords do not match');
            return false;
        }
        return true;
    }

    /**
     * Add error to errors object
     */
    addError(fieldName, message) {
        if (!this.errors[fieldName]) {
            this.errors[fieldName] = [];
        }
        this.errors[fieldName].push(message);
    }

    /**
     * Get field label
     */
    getFieldLabel(field) {
        const label = this.form.querySelector(`label[for="${field.id}"]`);
        return label ? label.textContent.replace('*', '').trim() : field.name;
    }

    /**
     * Display errors on form
     */
    displayErrors() {
        // Clear previous errors
        this.form.querySelectorAll('.error-message').forEach(el => el.remove());
        this.form.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });

        // Display new errors
        Object.keys(this.errors).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('border-red-500');
                
                const errorDiv = document.createElement('p');
                errorDiv.className = 'error-message mt-1 text-sm text-red-600';
                errorDiv.textContent = this.errors[fieldName][0];
                
                field.parentElement.appendChild(errorDiv);
            }
        });

        // Scroll to first error
        const firstError = this.form.querySelector('.border-red-500');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }

    /**
     * Check if form is valid
     */
    isValid() {
        return Object.keys(this.errors).length === 0;
    }

    /**
     * Clear all errors
     */
    clearErrors() {
        this.errors = {};
        this.form.querySelectorAll('.error-message').forEach(el => el.remove());
        this.form.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });
    }
}

/**
 * Initialize form validation on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add real-time validation to all forms with data-validate attribute
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const validator = new FormValidator(form);
            
            // Get validation rules from data attributes
            form.querySelectorAll('[data-required]').forEach(field => {
                validator.validateRequired(field.name);
            });

            form.querySelectorAll('[data-email]').forEach(field => {
                validator.validateEmail(field.name);
            });

            form.querySelectorAll('[data-phone]').forEach(field => {
                validator.validatePhone(field.name);
            });

            form.querySelectorAll('[data-min-length]').forEach(field => {
                validator.validateMinLength(field.name, parseInt(field.dataset.minLength));
            });

            form.querySelectorAll('[data-max-length]').forEach(field => {
                validator.validateMaxLength(field.name, parseInt(field.dataset.maxLength));
            });

            form.querySelectorAll('[data-numeric]').forEach(field => {
                validator.validateNumeric(field.name);
            });

            form.querySelectorAll('[data-min]').forEach(field => {
                validator.validateMin(field.name, parseFloat(field.dataset.min));
            });

            form.querySelectorAll('[data-max]').forEach(field => {
                validator.validateMax(field.name, parseFloat(field.dataset.max));
            });

            if (!validator.isValid()) {
                e.preventDefault();
                validator.displayErrors();
            }
        });

        // Clear error on field change
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('border-red-500');
                const errorMsg = this.parentElement.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        });
    });
});
