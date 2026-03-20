(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner(0);
    
    
    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 45) {
            $('.nav-bar').addClass('sticky-top shadow-sm');
        } else {
            $('.nav-bar').removeClass('sticky-top shadow-sm');
        }
    });


    // Hero Header carousel
    $(".header-carousel").owlCarousel({
        items: 1,
        autoplay: true,
        smartSpeed: 2000,
        center: false,
        dots: false,
        loop: true,
        margin: 0,
        nav : true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ]
    });


    // ProductList carousel
    $(".productList-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 2000,
        dots: false,
        loop: true,
        margin: 25,
        nav : true,
        navText : [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>'
        ],
        responsiveClass: true,
        responsive: {
            0:{
                items:1
            },
            576:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:2
            },
            1200:{
                items:3
            }
        }
    });

    // ProductList categories carousel
    $(".productImg-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        dots: false,
        loop: true,
        items: 1,
        margin: 25,
        nav : true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ]
    });


    // Single Products carousel
    $(".single-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        dots: true,
        dotsData: true,
        loop: true,
        items: 1,
        nav : true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ]
    });


    // ProductList carousel
    $(".related-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        dots: false,
        loop: true,
        margin: 25,
        nav : true,
        navText : [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>'
        ],
        responsiveClass: true,
        responsive: {
            0:{
                items:1
            },
            576:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            },
            1200:{
                items:4
            }
        }
    });



    // Product Quantity
    $('.quantity button').on('click', function () {
        var button = $(this);
        var oldValue = button.parent().parent().find('input').val();
        if (button.hasClass('btn-plus')) {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            if (oldValue > 0) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 0;
            }
        }
        button.parent().parent().find('input').val(newVal);
    });

    // Product quick view modal
    const quickViewModalElement = document.getElementById('theme1QuickViewModal');
    if (quickViewModalElement && window.bootstrap) {
        const quickViewModal = new window.bootstrap.Modal(quickViewModalElement);
        const quickViewSelectors = '.product-details a, .related-details a, .products-mini-icon a, [data-theme1-quick-view="true"]';
        let lastQuickViewTrigger = null;

        const quickViewElements = {
            image: quickViewModalElement.querySelector('[data-quick-view-image]'),
            badge: quickViewModalElement.querySelector('[data-quick-view-badge]'),
            categoryLink: quickViewModalElement.querySelector('[data-quick-view-category-link]'),
            categoryText: quickViewModalElement.querySelector('[data-quick-view-category-text]'),
            title: quickViewModalElement.querySelector('[data-quick-view-title]'),
            stars: quickViewModalElement.querySelector('[data-quick-view-stars]'),
            ratingText: quickViewModalElement.querySelector('[data-quick-view-rating-text]'),
            pricing: quickViewModalElement.querySelector('[data-quick-view-pricing]'),
            price: quickViewModalElement.querySelector('[data-quick-view-price]'),
            oldPrice: quickViewModalElement.querySelector('[data-quick-view-old-price]'),
            description: quickViewModalElement.querySelector('[data-quick-view-description]'),
            status: quickViewModalElement.querySelector('[data-quick-view-status]'),
            stock: quickViewModalElement.querySelector('[data-quick-view-stock]'),
            cart: quickViewModalElement.querySelector('[data-quick-view-cart]'),
            wishlist: quickViewModalElement.querySelector('[data-quick-view-wishlist]'),
            details: quickViewModalElement.querySelector('[data-quick-view-details]')
        };

        const normalizeText = (value) => (value || '').replace(/\s+/g, ' ').trim();
        const getText = (element) => normalizeText(element ? element.textContent : '');
        const getHref = (element) => {
            const href = element && element.getAttribute ? element.getAttribute('href') : '';
            return href && href !== '#' ? href : '';
        };
        const hasValue = (value) => normalizeText(String(value || '')) !== '';
        const getCardElement = (trigger) => trigger.closest('.product-item, .products-mini-item, .related-item');
        const getFilledRating = (card) => Array.from(card.querySelectorAll('.fa-star')).filter(function (icon) {
            return icon.classList.contains('text-primary') || icon.classList.contains('text-secondary');
        }).length;
        const getPriceElement = (card) => {
            const oldPriceElement = card.querySelector('del');
            if (oldPriceElement && oldPriceElement.nextElementSibling && oldPriceElement.nextElementSibling.matches('span')) {
                return oldPriceElement.nextElementSibling;
            }

            return card.querySelector('.text-primary.fs-5, .text-primary.fs-4, .text-primary');
        };
        const parseProductPayload = (trigger) => {
            const payloadNode = trigger.closest('[data-theme1-product]');
            if (!payloadNode) {
                return {};
            }

            try {
                return JSON.parse(payloadNode.getAttribute('data-theme1-product') || '{}');
            } catch (error) {
                return {};
            }
        };
        const mergeProductData = (fallbackData, payloadData) => {
            const mergedData = Object.assign({}, fallbackData);

            Object.keys(payloadData || {}).forEach(function (key) {
                if (hasValue(payloadData[key])) {
                    mergedData[key] = payloadData[key];
                }
            });

            return mergedData;
        };
        const buildFallbackDescription = (productData) => {
            if (hasValue(productData.excerpt)) {
                return productData.excerpt;
            }

            const productName = hasValue(productData.name) ? productData.name : 'this product';

            if (hasValue(productData.price) || hasValue(productData.old_price)) {
                return 'Review ' + productName + ' with the current pricing and a quick product summary before opening the full page.';
            }

            return 'Preview ' + productName + ' before continuing to the full product page.';
        };
        const extractProductData = (trigger) => {
            const card = getCardElement(trigger);
            if (!card) {
                return {};
            }

            const imageElement = card.querySelector('.product-item-inner-item img, .products-mini-img img, .related-item-inner-item img, img');
            const titleElement = card.querySelector('.text-center .h4, .products-mini-content .h4');
            const categoryElement = card.querySelector('.text-center .mb-2, .products-mini-content .mb-2');
            const descriptionElement = card.querySelector('p.text-muted');
            const oldPriceElement = card.querySelector('del');
            const priceElement = getPriceElement(card);
            const badgeElement = card.querySelector('.product-new, .product-sale, .related-new');

            return mergeProductData({
                name: getText(titleElement) || normalizeText(imageElement ? imageElement.getAttribute('alt') : '') || 'Product preview',
                category: getText(categoryElement),
                category_url: getHref(categoryElement),
                image: imageElement ? imageElement.getAttribute('src') : '',
                image_alt: normalizeText(imageElement ? imageElement.getAttribute('alt') : '') || getText(titleElement) || 'Product image',
                price: getText(priceElement),
                old_price: getText(oldPriceElement),
                badge: getText(badgeElement),
                rating: getFilledRating(card),
                excerpt: getText(descriptionElement),
                stock: '',
                status: '',
                detail_url: getHref(titleElement) || getHref(trigger),
                cart_url: quickViewModalElement.getAttribute('data-cart-url') || '',
                wishlist_url: quickViewModalElement.getAttribute('data-wishlist-url') || ''
            }, parseProductPayload(trigger));
        };
        const renderRating = (ratingValue) => {
            const rating = Math.max(0, Math.min(5, parseInt(ratingValue, 10) || 0));

            quickViewElements.stars.innerHTML = Array.from({ length: 5 }, function (_, index) {
                return '<i class="fas fa-star' + (index < rating ? ' is-filled' : '') + '"></i>';
            }).join('');

            quickViewElements.stars.setAttribute('aria-label', rating > 0 ? rating + ' out of 5 stars' : 'No rating available');
            quickViewElements.ratingText.hidden = rating === 0;
            quickViewElements.ratingText.textContent = rating > 0 ? rating + '/5 rating' : '';
        };
        const setChip = (element, value) => {
            const normalizedValue = normalizeText(String(value || ''));
            element.hidden = normalizedValue === '';
            element.textContent = normalizedValue;
        };
        const populateQuickView = (productData) => {
            const productName = hasValue(productData.name) ? productData.name : 'Product preview';
            const categoryName = normalizeText(productData.category);
            const categoryUrl = getHref({ getAttribute: function () { return productData.category_url || ''; } });
            const detailUrl = getHref({ getAttribute: function () { return productData.detail_url || ''; } });
            const cartUrl = getHref({ getAttribute: function () { return productData.cart_url || ''; } }) || quickViewModalElement.getAttribute('data-cart-url') || '';
            const wishlistUrl = getHref({ getAttribute: function () { return productData.wishlist_url || ''; } }) || quickViewModalElement.getAttribute('data-wishlist-url') || '';
            const imageSource = productData.image || '';

            quickViewElements.image.src = imageSource;
            quickViewElements.image.alt = normalizeText(productData.image_alt) || productName;
            quickViewElements.title.textContent = productName;

            if (categoryName && categoryUrl) {
                quickViewElements.categoryLink.hidden = false;
                quickViewElements.categoryLink.href = categoryUrl;
                quickViewElements.categoryLink.textContent = categoryName;
                quickViewElements.categoryText.hidden = true;
                quickViewElements.categoryText.textContent = '';
            } else if (categoryName) {
                quickViewElements.categoryLink.hidden = true;
                quickViewElements.categoryLink.removeAttribute('href');
                quickViewElements.categoryLink.textContent = '';
                quickViewElements.categoryText.hidden = false;
                quickViewElements.categoryText.textContent = categoryName;
            } else {
                quickViewElements.categoryLink.hidden = true;
                quickViewElements.categoryLink.removeAttribute('href');
                quickViewElements.categoryLink.textContent = '';
                quickViewElements.categoryText.hidden = true;
                quickViewElements.categoryText.textContent = '';
            }

            quickViewElements.badge.hidden = !hasValue(productData.badge);
            quickViewElements.badge.textContent = normalizeText(productData.badge);

            quickViewElements.pricing.hidden = !hasValue(productData.price) && !hasValue(productData.old_price);
            quickViewElements.price.textContent = normalizeText(productData.price);
            quickViewElements.oldPrice.hidden = !hasValue(productData.old_price);
            quickViewElements.oldPrice.textContent = normalizeText(productData.old_price);

            quickViewElements.description.textContent = buildFallbackDescription(productData);
            renderRating(productData.rating);
            setChip(quickViewElements.status, productData.status);
            setChip(quickViewElements.stock, productData.stock);

            quickViewElements.cart.href = cartUrl || '#';
            quickViewElements.wishlist.href = wishlistUrl || '#';

            if (detailUrl) {
                quickViewElements.details.hidden = false;
                quickViewElements.details.href = detailUrl;
            } else {
                quickViewElements.details.hidden = true;
                quickViewElements.details.removeAttribute('href');
            }
        };

        document.querySelectorAll(quickViewSelectors).forEach(function (trigger) {
            trigger.setAttribute('aria-haspopup', 'dialog');
            if (!trigger.getAttribute('aria-label')) {
                trigger.setAttribute('aria-label', 'Open product quick view');
            }
        });

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest(quickViewSelectors);
            if (!trigger) {
                return;
            }

            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const productData = extractProductData(trigger);
            if (!hasValue(productData.name) && !hasValue(productData.image)) {
                return;
            }

            event.preventDefault();
            lastQuickViewTrigger = trigger;
            populateQuickView(productData);
            quickViewModal.show();
        });

        quickViewModalElement.addEventListener('hidden.bs.modal', function () {
            if (lastQuickViewTrigger && typeof lastQuickViewTrigger.focus === 'function') {
                lastQuickViewTrigger.focus();
            }
        });
    }


    
   // Back to top button
   $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
        $('.back-to-top').fadeIn('slow');
    } else {
        $('.back-to-top').fadeOut('slow');
    }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


   

})(jQuery);
