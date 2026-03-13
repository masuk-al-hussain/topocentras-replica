/**
 * Mobile Menu Toggle Functionality
 */
(function() {
    'use strict';

    function initMobileMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuOverlay = document.querySelector('.mobile-menu-overlay');
        const menuClose = document.querySelector('.mobile-menu-close');
        const body = document.body;

        if (!menuToggle || !mobileMenu || !menuOverlay || !menuClose) {
            return;
        }

        function openMenu() {
            mobileMenu.classList.add('active');
            menuOverlay.classList.add('active');
            menuToggle.classList.add('active');
            body.classList.add('mobile-menu-open');
        }

        function closeMenu() {
            mobileMenu.classList.remove('active');
            menuOverlay.classList.remove('active');
            menuToggle.classList.remove('active');
            body.classList.remove('mobile-menu-open');
        }

        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (mobileMenu.classList.contains('active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        menuClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
        });

        menuOverlay.addEventListener('click', function() {
            closeMenu();
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                closeMenu();
            }
        });

        // Prevent menu links from closing the menu if they have submenus
        const menuLinks = mobileMenu.querySelectorAll('.mobile-menu-item');
        menuLinks.forEach(function(link) {
            if (!link.classList.contains('mobile-menu-catalog')) {
                link.addEventListener('click', function() {
                    // Close menu when clicking on regular links
                    setTimeout(closeMenu, 100);
                });
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        initMobileMenu();
    }
})();
