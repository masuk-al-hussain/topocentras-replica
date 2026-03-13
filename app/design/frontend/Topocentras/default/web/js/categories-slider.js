require(['jquery', 'owlcarousel'], function($) {
    'use strict';
    
    $(document).ready(function() {
        if ($('.categories-container').length) {
            $('.categories-container').owlCarousel({
                loop: false,
                margin: 10,
                nav: true,
                dots: false,
                autoWidth: true,
                navText: ['<span aria-label="Previous slide"></span>', '<span aria-label="Next slide"></span>'],
                navClass: ['categories-slider-left-arrow', 'categories-slider-right-arrow'],
                responsive: {
                    0: {
                        items: 3,
                        autoWidth: false
                    },
                    480: {
                        items: 3,
                        autoWidth: false
                    },
                    768: {
                        items: 4,
                        autoWidth: false
                    },
                    992: {
                        items: 5,
                        autoWidth: true
                    },
                    1200: {
                        items: 7,
                        autoWidth: true
                    }
                },
                onInitialized: function() {
                    $('.categories-container .owl-stage').addClass('first-page');
                    
                    $('.categories-container img').each(function() {
                        $(this).css('opacity', '1');
                    });
                }
            });
        }
    });
});
