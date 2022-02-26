<?php

namespace YOOtheme\Theme\Wordpress;

use WooCommerce;
use YOOtheme\Config;
use YOOtheme\File;
use YOOtheme\Metadata;
use YOOtheme\Path;
use YOOtheme\Theme\Styler;

class WooCommerceListener
{
    /**
     * Styler imports.
     *
     * @param Styler $styler
     * @param array  $imports
     * @return array
     */
    public static function stylerImports(Styler $styler, $imports)
    {
        // ignore files from being compiled into theme.css
        if (!class_exists('WooCommerce', false)) {
            $woo = Path::get('../assets/less/woocommerce.less');

            foreach ($styler->resolveImports($woo) as $file => $content) {
                unset($imports[$file]);
            }
        }
        return $imports;
    }

    /**
     * Initialize customizer config.
     *
     * @param Config $config
     */
    public static function initCustomizer(Config $config)
    {
        $file = File::find("~theme/css/theme{.{$config('theme.id')},}.css");
        $compiled = strpos(File::getContents($file), '.woocommerce');
        $isWoocommerce = class_exists('WooCommerce', false);

        // check if theme css needs to be updated
        if ($isWoocommerce xor $compiled) {
            $config->set('customizer.sections.styler.update', true);
        }

        if ($isWoocommerce) {
            $config->set('woocommerce.cartPage', (int) wc_get_page_id('cart'));
            $config->addFile('customizer', Path::get('../config/customizer.json'));
            $config->update('customizer.sections.styler.ignore', function ($ignore = []) {
                if (($key = array_search('@woocommerce-*', $ignore)) !== false) {
                    unset($ignore[$key]);
                }
                return $ignore;
            });
        }
    }

    /**
     * Remove WooCommerce general style.
     *
     * @param mixed $styles
     */
    public static function removeStyle($styles)
    {
        unset(
            $styles['woocommerce-general'],
            $styles['woocommerce-layout'],
            $styles['woocommerce-smallscreen']
        );

        return $styles;
    }

    /**
     * Remove WooSelect style/script.
     */
    public static function removeSelect()
    {
        wp_dequeue_style('select2');
        wp_deregister_style('select2');

        wp_dequeue_script('selectWoo');
        wp_deregister_script('selectWoo');
    }

    /**
     * Use WooCommerce breadcrumb trail for its pages.
     *
     * @param mixed $items
     */
    public static function breadcrumbs($items)
    {
        if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
            return $items;
        }

        $breadcrumbs = new \WC_Breadcrumb();
        $breadcrumbs->generate();

        WooCommerce::instance()->structured_data->generate_breadcrumblist_data($breadcrumbs);

        return array_map(function ($item) {
            return ['name' => $item[0], 'link' => $item[1]];
        }, $breadcrumbs->get_breadcrumb());
    }

    /**
     * Add custom classes to <input> tags in review comment form.
     *
     * @param mixed $form
     */
    public static function reviewCommentFormArgs($form)
    {
        foreach ($form['fields'] as &$field) {
            $field = str_replace('<input ', '<input class="uk-input uk-form-width-large" ', $field);
        }

        return $form;
    }

    /**
     * Show the lowest price for variable product.
     *
     * @param Config               $config
     * @param string               $price
     * @param \WC_Product_Variable $product
     *
     * @return string
     */
    public static function variablePriceHtml(Config $config, $price, $product)
    {
        if (!$config('~theme.woocommerce.price.from')) {
            return $price;
        }

        $minPriceRegular = $product->get_variation_regular_price('min', true);
        $minPriceSale = $product->get_variation_sale_price('min', true);

        $price =
            $minPriceSale !== $minPriceRegular
                ? wc_format_sale_price($minPriceRegular, $minPriceSale)
                : wc_price($minPriceRegular);

        return static::fromPrice(
            $price,
            $product->get_variation_price('min', true),
            $product->get_variation_price('max', true)
        );
    }

    /**
     * Show the lowest price for grouped product.
     *
     * @param Config               $config
     * @param string               $price
     * @param \WC_Product_Variable $product
     * @param string[]             $childPrices
     *
     * @return string
     */
    public static function groupedPriceHtml(Config $config, $price, $product, $childPrices)
    {
        if (!$config('~theme.woocommerce.price.from')) {
            return $price;
        }

        $minPrice = min($childPrices);
        return static::fromPrice(wc_price($minPrice), $minPrice, max($childPrices));
    }

    protected static function fromPrice($price, $minPrice, $maxPrice)
    {
        return $minPrice !== $maxPrice
            ? '<span class="tm-price-from">' .
                    _x('from', 'min_price', 'yootheme') .
                    '</span> ' .
                    $price
            : $price;
    }

    /**
     * Add theme classes to product image gallery.
     *
     * @param Config $config
     *
     * @return string
     */
    public static function productThumbnailsColumns(Config $config)
    {
        return $config('~theme.woocommerce.product_thumbnails_columns', '4');
    }

    /**
     * Show sale price after regular price.
     *
     * @param Config $config
     * @param string $price
     * @param string $regular_price
     * @param string $sale_price
     *
     * @return string
     */
    public static function formatSalePrice(Config $config, $price, $regular_price, $sale_price)
    {
        if (!$config('~theme.woocommerce.price.sale_after_regular')) {
            return $price;
        }

        return sprintf(
            '<ins>%s</ins> <del aria-hidden="true">%s</del>',
            is_numeric($sale_price) ? wc_price($sale_price) : $sale_price,
            is_numeric($regular_price) ? wc_price($regular_price) : $regular_price
        );
    }

    /**
     * Replace variable price by chosen variation's price.
     *
     * @param Metadata $metadata
     */
    public static function beforeAddToCartForm(Metadata $metadata)
    {
        global $product;

        if ($product->is_type('variable')) {
            $metadata->set('script:woocommerce_price', [
                'src' => Path::get('../assets/js/variable-product.min.js'),
                'defer' => true,
            ]);
        }
    }

    /**
     * Add fragment with cart item count.
     *
     * @param mixed $fragments
     *
     * @return array
     */
    public static function addToCartFragments($fragments)
    {
        return $fragments + static::getCartQuantity();
    }

    /**
     * Filters the navigation menu items being returned.
     *
     * @param Config    $config
     * @param array     $items  the menu items, sorted by each menu item's menu order
     * @param \stdClass $args   an object containing wp_nav_menu() arguments
     *
     * @return array
     */
    public static function navMenuObjects(Config $config, $items, $args)
    {
        if (!\WC()->cart) {
            return $items;
        }

        foreach ($items as $item) {
            if ($item->object === 'page' && ((int) $item->object_id) === wc_get_page_id('cart')) {
                $quantities = static::getCartQuantity();
                $quantity =
                    $config("~theme.menu.items.{$item->ID}.woocommerce_cart_quantity") === 'badge'
                        ? $quantities['[data-cart-badge]']
                        : $quantities['[data-cart-brackets]'];

                $config->set("~theme.menu.items.{$item->ID}.title-suffix", $quantity);
            }
        }
        return $items;
    }

    /**
     * Adds custom ReviewWalker.
     *
     * @param array $args
     *
     * @return array
     */
    public static function productReviewListArgs($args)
    {
        return $args + ['walker' => new ReviewWalker()];
    }

    protected static function getCartQuantity()
    {
        $quantity = \WC()->cart->get_cart_contents_count();

        return [
            '[data-cart-badge]' => sprintf(
                '<span%s data-cart-badge>%s</span>',
                $quantity ? ' class="uk-badge"' : '',
                $quantity ?: ''
            ),
            '[data-cart-brackets]' =>
                '<span data-cart-brackets>' . ($quantity ? "({$quantity})" : '') . '</span>',
        ];
    }
}
