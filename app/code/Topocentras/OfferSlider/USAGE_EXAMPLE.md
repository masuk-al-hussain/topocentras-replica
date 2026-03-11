# Usage Example: Adding Offer Slider to Homepage

## Step 1: Create a Slider in Admin

1. Login to Magento Admin Panel
2. Navigate to **Offer Sliders > Manage Sliders**
3. Click **Add New Slider**
4. Fill in the form:
   - **Enable Slider**: Yes
   - **Slider Title**: "Stambiai buitinei technikai TOP kainos + nemokamas pristatymas"
   - **All Offers Link URL**: "/pasiulymas-electrolux-ir-aeg-stambiai-buitinei-technikai-top-kainos.html"
   - **All Offers Text**: "Visi pasiūlymai"
   - **Banner Image**: Upload your promotional banner
   - **Banner Link URL**: "/pasiulymas-electrolux-ir-aeg-stambiai-buitinei-technikai-top-kainos.html"
   - **Sort Order**: 10
5. Click **Save**

## Step 2: Add Products to the Slider

After saving the slider, you'll see the **Slider Products** section:

1. Click **Add Product**
2. Enter Product SKU: "181103000008" (for example)
3. Set Sort Order: 1
4. Click **Save**
5. Repeat for all products you want in the slider

Example products to add:
- SKU: 181103000008 - Garų surinktuvas ELECTROLUX LFG716R
- SKU: 181125000158 - Kaitlentė ELECTROLUX EHF6240XXK
- SKU: 171101000054 - Skalbimo mašina AEG LFR95967UE
- etc.

## Step 3: Add to Homepage Layout

Edit or create: `app/design/frontend/Topocentras/default/Magento_Theme/layout/cms_index_index.xml`

Add the following block where you want the slider to appear:

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="content">
            <!-- Add after brands block -->
            <block class="Topocentras\OfferSlider\Block\OfferSlider" 
                   name="topocentras.offer.slider" 
                   template="Topocentras_OfferSlider::slider.phtml"
                   after="topocentras.brands.block"/>
        </referenceContainer>
    </body>
</page>
```

## Step 4: Display Specific Slider by ID

If you want to display only a specific slider (e.g., slider ID 1):

```xml
<block class="Topocentras\OfferSlider\Block\OfferSlider" 
       name="topocentras.offer.slider.specific" 
       template="Topocentras_OfferSlider::slider.phtml">
    <arguments>
        <argument name="slider_id" xsi:type="string">1</argument>
    </arguments>
</block>
```

## Step 5: Display Multiple Sliders

To display all active sliders (no slider_id argument):

```xml
<block class="Topocentras\OfferSlider\Block\OfferSlider" 
       name="topocentras.offer.slider.all" 
       template="Topocentras_OfferSlider::slider.phtml"/>
```

This will render all active sliders in order of their sort_order.

## Step 6: Clear Cache

After making layout changes:

```bash
php bin/magento cache:flush
```

## Advanced: Custom Template

Create a custom template in your theme:

`app/design/frontend/Topocentras/default/Topocentras_OfferSlider/templates/custom-slider.phtml`

Then reference it in your layout:

```xml
<block class="Topocentras\OfferSlider\Block\OfferSlider" 
       name="topocentras.offer.slider.custom" 
       template="Topocentras_OfferSlider::custom-slider.phtml"/>
```

## Complete Homepage Example

Here's a complete example showing where to place the offer slider on the homepage:

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <head>
        <css src="css/slider.css"/>
        <css src="css/category-menu.css"/>
        <css src="css/banner-carousel.css"/>
        <css src="css/weekly-deals-slider.css"/>
        <script src="js/weekly-deals-slider.js"/>
        <css src="css/categories-slider.css"/>
        <css src="css/brands-block.css"/>
        <css src="css/popular-products-slider.css"/>
        <script src="js/popular-products-slider.js"/>
    </head>
    <body>
        <referenceBlock name="page.main.title" remove="true"/>
        <referenceBlock name="cms_page" remove="true"/>
        
        <referenceContainer name="content">
            <!-- Main Slider and Category Menu -->
            <container name="homepage.slider.wrapper" htmlTag="div" htmlClass="homepage-slider-wrapper" before="-">
                <block class="Magento\Framework\View\Element\Template" 
                       name="topocentras.category.menu" 
                       template="Magento_Theme::html/category-menu.phtml"
                       before="-"/>
                <container name="slider.carousel.column" htmlTag="div" htmlClass="slider-carousel-column">
                    <block class="Topocentras\Slider\Block\Slider" 
                           name="topocentras.slider" 
                           template="Magento_Theme::slider/main-slider.phtml"
                           before="-"/>
                    <block class="Magento\Framework\View\Element\Template" 
                           name="topocentras.banner.carousel" 
                           template="Magento_Theme::banner/carousel.phtml"
                           after="topocentras.slider"/>
                </container>
            </container>
            
            <!-- Weekly Deals Slider -->
            <block class="Magento\Framework\View\Element\Template" 
                   name="topocentras.weekly.deals" 
                   template="Magento_Theme::weekly-deals/slider.phtml"
                   after="homepage.slider.wrapper"/>
            
            <!-- Categories Slider -->
            <block class="Magento\Framework\View\Element\Template" 
                   name="topocentras.categories.slider" 
                   template="Magento_Theme::categories/slider.phtml"
                   after="topocentras.weekly.deals"/>
            
            <!-- Brands Block -->
            <block class="Magento\Framework\View\Element\Template" 
                   name="topocentras.brands.block" 
                   template="Magento_Theme::brands/block.phtml"
                   after="topocentras.categories.slider"/>
            
            <!-- NEW: Offer Slider (displays all active sliders) -->
            <block class="Topocentras\OfferSlider\Block\OfferSlider" 
                   name="topocentras.offer.slider" 
                   template="Topocentras_OfferSlider::slider.phtml"
                   after="topocentras.brands.block"/>
            
            <!-- Popular Products Slider -->
            <block class="Topocentras\PopularProducts\Block\PopularProducts" 
                   name="topocentras.popular.products" 
                   template="Magento_Theme::popular-products/slider.phtml"
                   after="topocentras.offer.slider"/>
        </referenceContainer>
    </body>
</page>
```

## Result

The slider will display with:
- Main title with "All Offers" link
- Banner image (if provided)
- Product carousel with Owl Carousel
- Navigation arrows
- Responsive design (1 item on mobile, 3 on tablet, 4 on desktop)

Each product shows:
- Product image
- Product name (linked)
- Price
- Hover effects
