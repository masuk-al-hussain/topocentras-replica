define(['jquery'], function($) {
    'use strict';

    return function(config, element) {
        var $slider = $(element);
        var $track = $slider.find('.slider-track');
        var $items = $track.find('.slider-item');
        var $prevBtn = $slider.find('.slider-prev');
        var $nextBtn = $slider.find('.slider-next');
        
        var currentIndex = 0;
        var itemsPerView = config.itemsPerView || 4;
        var totalItems = $items.length;
        var maxIndex = Math.max(0, totalItems - itemsPerView);
        
        // Responsive items per view
        function updateItemsPerView() {
            var width = $(window).width();
            if (width < 600) {
                itemsPerView = 1;
            } else if (width < 1000) {
                itemsPerView = 3;
            } else {
                itemsPerView = config.itemsPerView || 4;
            }
            maxIndex = Math.max(0, totalItems - itemsPerView);
            updateSlider();
        }
        
        function updateSlider() {
            var itemWidth = 100 / itemsPerView;
            $items.css('width', itemWidth + '%');
            
            var translateX = -(currentIndex * itemWidth);
            $track.css('transform', 'translateX(' + translateX + '%)');
            
            // Update button states
            $prevBtn.prop('disabled', currentIndex === 0);
            $nextBtn.prop('disabled', currentIndex >= maxIndex);
        }
        
        $prevBtn.on('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });
        
        $nextBtn.on('click', function() {
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSlider();
            }
        });
        
        $(window).on('resize', function() {
            updateItemsPerView();
        });
        
        // Initialize
        updateItemsPerView();
    };
});
