// Haupt-JavaScript-Datei

// Navbar Toggle für mobile Ansicht
function toggleNavbar() {
    const menu = document.querySelector('.navbar-menu');
    menu.classList.toggle('active');
}

// Modal-Funktionen
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Schließe Modal beim Klick außerhalb
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
}

// Bestätigungsdialog für Löschaktionen
document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Wirklich löschen?')) {
                e.preventDefault();
            }
        });
    });
});

// Auto-Hide Alerts nach 5 Sekunden
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});