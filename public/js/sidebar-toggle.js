// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function () {
    var shell = document.querySelector('[data-volunteer-shell]');
    var button = document.querySelector('[data-toggle-sidebar]');

    if (shell && button) {
        button.addEventListener('click', function () {
            shell.classList.toggle('sidebar-open');
        });
    }
});
