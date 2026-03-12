define([
    'jquery',
    'jquery/jquery.cookie'
], function ($) {
    'use strict';

    return function (config) {
        var COOKIE_NAME = 'recently_viewed_products';
        var MAX_PRODUCTS = 5;
        var productId = config.productId;

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
         * Add product to recently viewed
         */
        function addProductToRecentlyViewed(productId) {
            if (!productId) {
                return;
            }

            var ids = getRecentlyViewedIds();
            
            // Remove if already exists
            ids = ids.filter(function(id) {
                return id !== productId;
            });
            
            // Add to beginning (most recent first)
            ids.unshift(productId);
            
            // Limit to MAX_PRODUCTS
            if (ids.length > MAX_PRODUCTS) {
                ids = ids.slice(0, MAX_PRODUCTS);
            }
            
            saveRecentlyViewedIds(ids);
        }

        // Track the current product view
        if (productId) {
            addProductToRecentlyViewed(parseInt(productId, 10));
        }
    };
});
