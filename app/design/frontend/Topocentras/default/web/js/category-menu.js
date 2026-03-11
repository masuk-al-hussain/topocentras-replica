require(['jquery'], function($) {
    'use strict';

    $(document).ready(function() {
        const categoryMenu = $('.category-menu');
        const categoryItems = $('.category-item');

        categoryItems.each(function() {
            const item = $(this);
            const submenu = item.find('.category-submenu');

            item.on('mouseenter', function() {
                if (submenu.length) {
                    const menuRect = categoryMenu[0].getBoundingClientRect();
                    const menuLeft = menuRect.left + menuRect.width;
                    const menuTop = menuRect.top;
                    const menuHeight = menuRect.height;

                    submenu.css({
                        'left': menuLeft + 'px',
                        'top': menuTop + 'px',
                        'height': menuHeight + 'px'
                    });
                }
            });
        });

        $(window).on('scroll resize', function() {
            const activeSubmenu = $('.category-item:hover .category-submenu');
            if (activeSubmenu.length) {
                const menuRect = categoryMenu[0].getBoundingClientRect();
                const menuLeft = menuRect.left + menuRect.width;
                const menuTop = menuRect.top;
                const menuHeight = menuRect.height;

                activeSubmenu.css({
                    'left': menuLeft + 'px',
                    'top': menuTop + 'px',
                    'height': menuHeight + 'px'
                });
            }
        });
    });
});
