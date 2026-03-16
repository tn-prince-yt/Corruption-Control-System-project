// Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    console.log('Corruption Control System loaded');

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add any custom validation here if needed
        });
    });

    // Alert auto-hide
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });

    // Sidebar menu active state
    const currentPage = window.location.pathname.split('/').pop();
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    menuItems.forEach(item => {
        const href = item.getAttribute('href').split('/').pop();
        if (href === currentPage) {
            item.classList.add('active');
        }
    });
});

// Utility function to confirm delete
function confirmDelete(message) {
    return confirm(message || 'Are you sure?');
}

// Utility function to format date
function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

// Utility function to show/hide elements
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}
