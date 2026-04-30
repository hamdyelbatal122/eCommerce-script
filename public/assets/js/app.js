/**
 * ECommerce Marketplace - Custom Application JavaScript
 * Professional, responsive interactions and utilities
 */

// ============================================================================
// DOCUMENT READY - Initialize all features on page load
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    initializeAlerts();
    initializeSearch();
    initializeFormValidation();
    initializeTooltips();
    initializeNavigation();
});

// ============================================================================
// ALERTS - Auto-dismiss flash messages
// ============================================================================

function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alert && alert.parentElement) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
}

// ============================================================================
// SEARCH - Search functionality & Enter key handling
// ============================================================================

function initializeSearch() {
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    
    if (searchBtn && searchInput) {
        // Handle search button click
        searchBtn.addEventListener('click', function() {
            performSearch();
        });

        // Handle Enter key in search input
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
}

function performSearch() {
    const searchInput = document.getElementById('searchInput');
    const query = searchInput.value.trim();
    
    if (query) {
        window.location.href = '/search?q=' + encodeURIComponent(query);
    } else {
        showNotification('Please enter a search term', 'warning');
    }
}

// ============================================================================
// FORM VALIDATION - Bootstrap validation with feedback
// ============================================================================

function initializeFormValidation() {
    const forms = document.querySelectorAll('form[novalidate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                showNotification('Please correct the errors in the form', 'danger');
            }
            form.classList.add('was-validated');
        });
    });

    // Validate form inputs on blur
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });
    });
}

function validateInput(input) {
    const feedback = input.nextElementSibling;
    
    if (input.hasAttribute('required') && !input.value.trim()) {
        input.classList.add('is-invalid');
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.innerText = 'This field is required';
        }
        return false;
    } else if (input.type === 'email' && input.value && !isValidEmail(input.value)) {
        input.classList.add('is-invalid');
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.innerText = 'Please enter a valid email address';
        }
        return false;
    } else {
        input.classList.remove('is-invalid');
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.innerText = '';
        }
        return true;
    }
}

// ============================================================================
// TOOLTIPS - Bootstrap tooltips initialization
// ============================================================================

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// ============================================================================
// NAVIGATION - Mobile menu and scroll effects
// ============================================================================

function initializeNavigation() {
    // Highlight active nav link based on current URL
    const currentLocation = location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation) {
            link.classList.add('active');
        }
    });

    // Navbar scroll effect (add shadow when scrolled)
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('shadow-lg');
        } else {
            navbar.classList.remove('shadow-lg');
        }
    });
}

// ============================================================================
// API UTILITIES - Fetch helper with error handling
// ============================================================================

/**
 * Make an API call with proper error handling
 * @param {string} url - The API endpoint
 * @param {string} method - HTTP method (GET, POST, PUT, DELETE)
 * @param {object} data - Request body data
 * @returns {Promise} The response as JSON
 */
async function apiCall(url, method = 'GET', data = null) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
            },
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showNotification('An error occurred. Please try again.', 'danger');
        throw error;
    }
}

// ============================================================================
// NOTIFICATIONS - Toast-like message display
// ============================================================================

/**
 * Show a toast notification message
 * @param {string} message - Message to display
 * @param {string} type - Alert type (success, danger, warning, info)
 * @param {number} duration - Auto-dismiss duration in ms (0 = no auto-dismiss)
 */
function showNotification(message, type = 'info', duration = 5000) {
    const alertDiv = document.createElement('div');
    const alertType = type === 'error' ? 'danger' : type;
    const iconMap = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    alertDiv.className = `alert alert-${alertType} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <i class="fas fa-${iconMap[alertType] || 'info-circle'} me-2"></i>
        <strong>${message}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.main-content') || document.body;
    mainContent.insertBefore(alertDiv, mainContent.firstChild);

    // Auto-dismiss if duration > 0
    if (duration > 0) {
        setTimeout(() => {
            if (alertDiv && alertDiv.parentElement) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, duration);
    }
}

// ============================================================================
// FORM UTILITIES - Common form operations
// ============================================================================

/**
 * Show a loading state on a button
 * @param {element} button - The button element
 * @param {string} text - Loading text to display
 */
function setButtonLoading(button, text = 'Loading...') {
    button.disabled = true;
    button.innerText = text;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + text;
}

/**
 * Reset button to normal state
 * @param {element} button - The button element
 * @param {string} text - Original button text
 */
function resetButton(button, text = 'Submit') {
    button.disabled = false;
    button.innerText = text;
}

/**
 * Clear all form errors
 * @param {element} form - The form element
 */
function clearFormErrors(form) {
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.innerText = '';
        }
    });
}

// ============================================================================
// VALIDATION UTILITIES - Input validation helpers
// ============================================================================

/**
 * Validate email format
 * @param {string} email - Email address
 * @returns {boolean} True if valid
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate password strength
 * @param {string} password - Password to validate
 * @returns {object} Strength info
 */
function checkPasswordStrength(password) {
    let strength = 0;
    const feedback = [];

    if (password.length >= 8) strength++;
    else feedback.push('At least 8 characters');

    if (/[a-z]/.test(password)) strength++;
    else feedback.push('At least one lowercase letter');

    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('At least one uppercase letter');

    if (/[0-9]/.test(password)) strength++;
    else feedback.push('At least one number');

    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    else feedback.push('At least one special character');

    return {
        score: strength,
        level: strength < 2 ? 'weak' : strength < 4 ? 'medium' : 'strong',
        feedback: feedback
    };
}

// ============================================================================
// TABLE UTILITIES - Table manipulation helpers
// ============================================================================

/**
 * Delete a table row with confirmation
 * @param {element} button - The delete button
 * @param {string} itemName - Name of item to delete (for confirmation)
 */
function deleteTableRow(button, itemName = 'this item') {
    if (confirm(`Are you sure you want to delete ${itemName}?`)) {
        const row = button.closest('tr');
        row.style.opacity = '0.5';
        row.style.pointerEvents = 'none';
        
        // Proceed with form submission or API call
        const form = button.closest('form');
        if (form) {
            form.submit();
        }
    }
}

// ============================================================================
// DATE & TIME UTILITIES
// ============================================================================

/**
 * Format date to readable string
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date
 */
function formatDate(date) {
    const d = new Date(date);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
}

/**
 * Get relative time (e.g., "2 hours ago")
 * @param {string|Date} date - Date to compare
 * @returns {string} Relative time string
 */
function getRelativeTime(date) {
    const d = new Date(date);
    const now = new Date();
    const secondsDiff = Math.floor((now - d) / 1000);

    if (secondsDiff < 60) return 'just now';
    if (secondsDiff < 3600) return `${Math.floor(secondsDiff / 60)} minutes ago`;
    if (secondsDiff < 86400) return `${Math.floor(secondsDiff / 3600)} hours ago`;
    if (secondsDiff < 604800) return `${Math.floor(secondsDiff / 86400)} days ago`;
    
    return formatDate(date);
}

// ============================================================================
// UTILITY HELPERS - General utilities
// ============================================================================

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(err => {
        showNotification('Failed to copy', 'danger');
    });
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @param {string} currency - Currency code (default: USD)
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// ============================================================================
// Export functions for global use
// ============================================================================

window.AppUtilities = {
    apiCall,
    showNotification,
    setButtonLoading,
    resetButton,
    clearFormErrors,
    isValidEmail,
    checkPasswordStrength,
    deleteTableRow,
    formatDate,
    getRelativeTime,
    copyToClipboard,
    formatCurrency
};
