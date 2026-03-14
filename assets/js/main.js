// Auto-dismiss all alert messages after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.alert-success, .alert-error, .alert').forEach(function(alert) {
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert && alert.parentNode) {
                        alert.style.display = 'none';
                    }
                }, 500);
            }
        });
    }, 3000); // 3000ms = 3 seconds
});

// You can add other global JavaScript functions here