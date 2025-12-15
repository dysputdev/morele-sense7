/**
 * Main Theme Scripts
 *
 * @package Sense7\Theme
 */

(function($) {
    'use strict';

    /**
     * Mobile menu toggle
     */
    function initMobileMenu() {
        const menuToggle = $('.menu-toggle');
        const navigation = $('#site-navigation');

        menuToggle.on('click', function() {
            navigation.toggleClass('toggled');
            $(this).attr('aria-expanded', navigation.hasClass('toggled'));
        });
    }

    /**
     * Smooth scroll for anchor links
     */
    function initSmoothScroll() {
        $('a[href*="#"]:not([href="#"])').on('click', function() {
            if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') &&
                location.hostname === this.hostname) {

                let target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                    return false;
                }
            }
        });
    }

    /**
     * Skip link focus fix
     */
    function initSkipLinkFocus() {
        const isIe = /(trident|msie)/i.test(navigator.userAgent);

        if (isIe && document.getElementById && window.addEventListener) {
            window.addEventListener('hashchange', function() {
                const id = location.hash.substring(1);
                let element;

                if (!(/^[A-z0-9_-]+$/.test(id))) {
                    return;
                }

                element = document.getElementById(id);

                if (element) {
                    if (!(/^(?:a|select|input|button|textarea)$/i.test(element.tagName))) {
                        element.tabIndex = -1;
                    }
                    element.focus();
                }
            }, false);
        }
    }

    /**
     * Initialize all functions
     */
    $(document).ready(function() {
        initMobileMenu();
        initSmoothScroll();
        initSkipLinkFocus();

        // Add your custom initialization code here
        console.log('Sense7 Theme loaded');
    });

})(jQuery);
