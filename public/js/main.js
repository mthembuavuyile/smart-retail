/**
 * Main client-side script for the Smart Retail System storefront.
 *
 * Handles:
 *  - Mobile navigation toggle
 *  - Confirmation dialogs on destructive actions (cart removal)
 *  - Page fade-in animation trigger
 */
document.addEventListener('DOMContentLoaded', function () {

    // ----------------------------------------------------------------
    // Mobile Navigation Toggle
    // ----------------------------------------------------------------
    var menuToggle = document.getElementById('menuToggle');
    var navLinks   = document.getElementById('navLinks');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
        });

        // Close the menu when a link is tapped on mobile
        var links = navLinks.querySelectorAll('a');
        links.forEach(function (link) {
            link.addEventListener('click', function () {
                navLinks.classList.remove('open');
            });
        });
    }

    // ----------------------------------------------------------------
    // Confirm before removing cart items
    // ----------------------------------------------------------------
    var removeButtons = document.querySelectorAll('.btn-remove');

    removeButtons.forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            if (!confirm('Remove this item from your cart?')) {
                event.preventDefault();
            }
        });
    });

    // ----------------------------------------------------------------
    // Fade-in animation on page load
    // ----------------------------------------------------------------
    var fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(function (el) {
        el.style.opacity = '0';
        requestAnimationFrame(function () {
            el.style.opacity = '';
        });
    });

});
