require(['jquery'], function($) {
    'use strict';
    
    $(document).ready(function() {
        const slider = document.querySelector('.popular-products-slider');
        if (!slider) return;
        
        const track = slider.querySelector('.products-track');
        const prevBtn = slider.querySelector('.slider-nav.prev');
        const nextBtn = slider.querySelector('.slider-nav.next');
        
        if (!track) return;
        
        // Get single card width for scrolling
        function getCardWidth() {
            const card = track.querySelector('.product-card');
            return card ? card.offsetWidth + 20 : 220; // 20px for gap
        }
        
        // Scroll to position
        function scrollToPosition(position) {
            track.scrollTo({
                left: position,
                behavior: 'smooth'
            });
        }
        
        // Scroll previous
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                scrollToPosition(track.scrollLeft - getCardWidth());
            });
        }
        
        // Scroll next
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                scrollToPosition(track.scrollLeft + getCardWidth());
            });
        }
        
        // Update button states based on scroll position
        function updateButtons() {
            const scrollLeft = track.scrollLeft;
            const maxScroll = track.scrollWidth - track.clientWidth;
            
            // Disable/enable prev button
            if (prevBtn) {
                if (scrollLeft <= 0) {
                    prevBtn.disabled = true;
                    prevBtn.style.opacity = '0.3';
                } else {
                    prevBtn.disabled = false;
                    prevBtn.style.opacity = '1';
                }
            }
            
            // Disable/enable next button
            if (nextBtn) {
                if (scrollLeft >= maxScroll - 1) {
                    nextBtn.disabled = true;
                    nextBtn.style.opacity = '0.3';
                } else {
                    nextBtn.disabled = false;
                    nextBtn.style.opacity = '1';
                }
            }
        }
        
        // Listen to scroll events
        track.addEventListener('scroll', updateButtons);
        
        // Initial button state
        updateButtons();
        
        // Update on window resize
        window.addEventListener('resize', updateButtons);
        
        // Add to cart functionality
        $(document).on('click', '.lupa-add-to-cart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const productId = $(this).data('product-id');
            const productSku = $(this).data('product-sku');
            console.log('Add to cart clicked for product:', productId, productSku);
            // Add your add to cart logic here
            // Example: window.location.href = '/checkout/cart/add?product=' + productId;
        });
        
        // Wishlist functionality
        $(document).on('click', '.WishlistButton-container-jZ9', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $card = $(this).closest('.product-card');
            const productId = $card.find('.lupa-add-to-cart').data('product-id');
            
            $(this).toggleClass('active');
            const $svg = $(this).find('svg');
            
            if ($(this).hasClass('active')) {
                $svg.attr('fill', '#e31e24');
                console.log('Added to wishlist:', productId);
                // Add your wishlist add logic here
            } else {
                $svg.attr('fill', 'none');
                console.log('Removed from wishlist:', productId);
                // Add your wishlist remove logic here
            }
        });
        
        // Compare functionality
        $(document).on('click', '.Compare-compareContainer-uDf', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const productId = $(this).data('product-id');
            const $checkbox = $(this).find('.Checkbox-checkBox-1fD');
            
            $(this).toggleClass('active');
            
            if ($(this).hasClass('active')) {
                $checkbox.css({
                    'background-color': '#0a509b',
                    'border-color': '#0a509b'
                });
                console.log('Added to compare:', productId);
                // Add your compare add logic here
            } else {
                $checkbox.css({
                    'background-color': 'transparent',
                    'border-color': '#d9d7c1'
                });
                console.log('Removed from compare:', productId);
                // Add your compare remove logic here
            }
        });
    });
});
