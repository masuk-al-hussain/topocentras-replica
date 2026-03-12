define([
    'jquery',
    'jquery/jquery.cookie'
], function ($) {
    'use strict';

    return function (config, element) {
        var $container = $(element);
        var $removeAllBtn = $container.find('[data-remove-all]');
        var COOKIE_NAME = 'recently_viewed_products';
        var MAX_PRODUCTS = 5;

        /**
         * Get recently viewed product IDs from cookie
         */
        function getRecentlyViewedIds() {
            var cookie = $.cookie(COOKIE_NAME);
            if (!cookie) {
                return [];
            }
            try {
                return JSON.parse(decodeURIComponent(cookie));
            } catch (e) {
                return [];
            }
        }

        /**
         * Save recently viewed product IDs to cookie
         */
        function saveRecentlyViewedIds(ids) {
            var cookieValue = encodeURIComponent(JSON.stringify(ids));
            $.cookie(COOKIE_NAME, cookieValue, {
                expires: 30,
                path: '/'
            });
        }

        /**
         * Remove all recently viewed products
         */
        function removeAllRecentlyViewed() {
            $.removeCookie(COOKIE_NAME, { path: '/' });
            
            // Hide the container with animation
            $container.fadeOut(300, function() {
                $container.remove();
            });
        }

        // Initialize
        if ($removeAllBtn.length) {
            $removeAllBtn.on('click', function(e) {
                e.preventDefault();
                
                if (confirm('Ar tikrai norite pašalinti visas peržiūrėtas prekes?')) {
                    removeAllRecentlyViewed();
                }
            });
        }
    };
});
