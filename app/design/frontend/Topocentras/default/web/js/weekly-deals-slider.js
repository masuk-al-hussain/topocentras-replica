require(['jquery'], function($) {
    'use strict';
    
    $(document).ready(function() {
        const sliders = document.querySelectorAll('[data-slider-cards]');
        
        sliders.forEach(slider => {
            const track = slider.querySelector('[data-slider-track]');
            const prevButton = slider.querySelector('[data-slider-prev]');
            const nextButton = slider.querySelector('[data-slider-next]');
            
            if (!track || !prevButton || !nextButton) return;
            
            const slides = track.children;
            
            function updateButtons() {
                const isAtStart = track.scrollLeft <= 0;
                const isAtEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
                
                prevButton.disabled = isAtStart;
                nextButton.disabled = isAtEnd;
            }
            
            function getSlideWidth() {
                return slides[0] ? slides[0].offsetWidth : 0;
            }
            
            prevButton.addEventListener('click', () => {
                const slideWidth = getSlideWidth();
                track.scrollBy({
                    left: -slideWidth,
                    behavior: 'smooth'
                });
            });
            
            nextButton.addEventListener('click', () => {
                const slideWidth = getSlideWidth();
                track.scrollBy({
                    left: slideWidth,
                    behavior: 'smooth'
                });
            });
            
            // Update buttons on scroll
            track.addEventListener('scroll', () => {
                updateButtons();
            });
            
            // Update buttons on window resize
            window.addEventListener('resize', () => {
                updateButtons();
            });
            
            updateButtons();
        });
    });
});
