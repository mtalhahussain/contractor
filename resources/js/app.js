import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Sidebar hover → shift navbar (JS needed because navbar is DOM sibling BEFORE sidebar)
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.main-sidebar');
    if (!sidebar) return;
    sidebar.addEventListener('mouseenter', function () {
        if (document.body.classList.contains('sidebar-collapse')) {
            document.body.classList.add('sidebar-hovered');
        }
    });
    sidebar.addEventListener('mouseleave', function () {
        document.body.classList.remove('sidebar-hovered');
    });
});
