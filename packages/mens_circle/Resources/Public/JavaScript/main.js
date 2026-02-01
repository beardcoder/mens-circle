/**
 * Männerkreis Niederbayern — Main JavaScript
 * Handles mobile navigation toggle and flash message auto-dismiss.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Mobile Navigation Toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function () {
            const isHidden = mobileMenu.classList.contains('hidden');

            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('block');
            } else {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('block');
            }

            // Toggle hamburger / X icon
            const openIcon = mobileMenuToggle.querySelector('.icon-open');
            const closeIcon = mobileMenuToggle.querySelector('.icon-close');

            if (openIcon && closeIcon) {
                openIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            }
        });

        // Close mobile menu on outside click
        document.addEventListener('click', function (event) {
            if (!mobileMenuToggle.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('block');
            }
        });
    }

    // Flash Messages — auto-dismiss after animation completes
    const flashMessages = document.querySelectorAll('.flash-message');

    flashMessages.forEach(function (message) {
        message.addEventListener('animationend', function () {
            message.remove();
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (event) {
            event.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));

            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }
        });
    });
});
