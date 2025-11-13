// Custom JavaScript for Astrology & Numerology Website
console.log("Main JavaScript file loaded.");

document.addEventListener('DOMContentLoaded', function () {
    // Code to run after the DOM is fully loaded

    // Active navigation link highlighting
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const currentAction = urlParams.get('action') || 'home'; // Default to home if no action

        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        navLinks.forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin); // Ensure full URL for comparison
            const linkAction = linkUrl.searchParams.get('action') || 'home';

            // Remove existing active classes
            link.classList.remove('active');
            link.removeAttribute('aria-current');

            if (linkAction === currentAction) {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            }
        });
         // Special case for the brand link if it's a home link and current action is home
        const brandLink = document.querySelector('.navbar-brand');
        if (brandLink) {
            const brandLinkUrl = new URL(brandLink.href, window.location.origin);
            const brandLinkAction = brandLinkUrl.searchParams.get('action') || 'home';
            if (currentAction === 'home' && brandLinkAction === 'home') {
                // If on home page, and a nav link also points to home, it might get 'active' too.
                // This logic is fine, often multiple links to home can be active.
            }
        }

    } catch (e) {
        console.error("Error setting active nav link:", e);
    }


    // Tooltip initialization (if you use Bootstrap tooltips)
    // Example:
    // const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    // tooltipTriggerList.forEach(tooltipTriggerEl => {
    //   new bootstrap.Tooltip(tooltipTriggerEl);
    // });

});
