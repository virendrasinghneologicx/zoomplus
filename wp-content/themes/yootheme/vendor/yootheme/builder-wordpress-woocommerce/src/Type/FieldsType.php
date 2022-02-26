<?php

namespace YOOtheme\Builder\Wordpress\Woocommerce\Type;

use YOOtheme\Builder\Wordpress\Source\Type\CustomPostQueryType;
use YOOtheme\Builder\Wordpress\Woocommerce\Helper;
use function YOOtheme\trans;
use YOOtheme\View\HtmlElement;

class FieldsType
{
    public static function config()
    {
        return [
            'fields' => [
                'sku' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => __('SKU', 'woocommerce'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::sku',
                    ],
                ],
                'price' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => __('Price', 'woocommerce'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::price',
                    ],
                ],
                'stock' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => __('Stock', 'woocommerce'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::stock',
                    ],
                ],
                'rating' => [
                    'type' => 'String',
                    'args' => [
                        'link' => [
                            'type' => 'Boolean',
                        ],
                    ],
                    'metadata' => [
                        'label' => trans('Rating'),
                        'group' => 'WooCommerce',
                        'arguments' => [
                            'link' => [
                                'label' => trans('Display'),
                                'description' => trans('Show or hide the reviews link.'),
                                'type' => 'checkbox',
                                'text' => trans('Show reviews link'),
                                'default' => true,
                            ],
                        ],
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::rating',
                    ],
                ],
                'on_sale' => [
                    'type' => 'Boolean',
                    'metadata' => [
                        'label' => __('On Sale', 'woocommerce'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::is_on_sale',
                    ],
                ],
                'total_sales' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => __('Total Sales', 'woocommerce'),
                        'group' => 'WooCommerce',
                    ],
                ],
                'add_to_cart_url' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Add to Cart Link'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::add_to_cart_url',
                    ],
                ],
                'add_to_cart_text' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Add to Cart Text'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::add_to_cart_text',
                    ],
                ],
                'additional_information' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Additional Information'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::additional_information',
                    ],
                ],
                'attributes' => [
                    'type' => ['listOf' => 'AttributeField'],
                    'metadata' => [
                        'label' => trans('Attributes'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::attributes',
                    ],
                ],
                'gallery_image_ids' => [
                    'type' => ['listOf' => 'Attachment'],
                    'metadata' => [
                        'label' => trans('Product Gallery'),
                        'group' => 'WooCommerce',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::galleryImageIds',
                    ],
                ],
                'upsell_products' => [
                    'type' => ['listOf' => 'Product'],
                    'args' => [
                        'offset' => [
                            'type' => 'Int',
                        ],
                        'limit' => [
                            'type' => 'Int',
                        ],
                        'order' => [
                            'type' => 'String',
                        ],
                        'order_direction' => [
                            'type' => 'String',
                        ],
                        'order_alphanum' => [
                            'type' => 'Boolean',
                        ],
                    ],
                    'metadata' => [
                        'label' => trans('Upsell Products'),
                        'group' => 'WooCommerce',
                        'arguments' => [
                            '_offset' => [
                                'type' => 'grid',
                                'width' => '1-2',
                                'fields' => [
                                    'offset' => [
                                        'label' => trans('Start'),
                                        'type' => 'number',
                                        'default' => 0,
                                        'modifier' => 1,
                                        'attrs' => [
                                            'min' => 1,
                                            'required' => true,
                                        ],
                                    ],
                                    'limit' => [
                                        'label' => trans('Quantity'),
                                        'type' => 'limit',
                                        'default' => 10,
                                        'attrs' => [
                                            'min' => 1,
                                        ],
                                    ],
                                ],
                            ],

                            '_order' => [
                                'type' => 'grid',
                                'width' => '1-2',
                                'fields' => [
                                    'order' => [
                                        'label' => trans('Order'),
                                        'type' => 'select',
                                        'default' => 'rand',
                                        'options' => [
                                            ['evaluate' => 'config.sources.postTypeOrderOptions'],
                                            ['evaluate' => 'config.sources.productOrderOptions'],
                                        ],
                                    ],
                                    'order_direction' => [
                                        'label' => trans('Direction'),
                                        'type' => 'select',
                                        'default' => 'DESC',
                                        'options' => [
                                            trans('Ascending') => 'ASC',
                                            trans('Descending') => 'DESC',
                                        ],
                                    ],
                                ],
                            ],

                            'order_alphanum' => [
                                'text' => trans('Alphanumeric Ordering'),
                                'type' => 'checkbox',
                            ],
                        ],
                        'directives' => [],
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::upsell_products',
                    ],
                ],
                'cross_sell_products' => [
                    'type' => ['listOf' => 'Product'],
                    'args' => [
                        'offset' => [
                            'type' => 'Int',
                        ],
                        'limit' => [
                            'type' => 'Int',
                        ],
                        'order' => [
                            'type' => 'String',
                        ],
                        'order_direction' => [
                            'type' => 'String',
                        ],
                        'order_alphanum' => [
                            'type' => 'Boolean',
                        ],
                    ],
                    'metadata' => [
                        'label' => trans('Cross-Sell Products'),
                        'group' => 'WooCommerce',
                        'arguments' => [
                            '_offset' => [
                                'type' => 'grid',
                                'width' => '1-2',
                                'fields' => [
                                    'offset' => [
                                        'label' => trans('Start'),
                                        'type' => 'number',
                                        'default' => 0,
                                        'modifier' => 1,
                                        'attrs' => [
                                            'min' => 1,
                                            'required' => true,
                                        ],
                                    ],
                                    'limit' => [
                                        'label' => trans('Quantity'),
                                        'type' => 'limit',
                                        'default' => 10,
                                        'attrs' => [
                                            'min' => 1,
                                        ],
                                    ],
                                ],
                            ],

                            '_order' => [
                                'type' => 'grid',
                                'width' => '1-2',
                                'fields' => [
                                    'order' => [
                                        'label' => trans('Order'),
                                        'type' => 'select',
                                        'default' => 'rand',
                                        'options' => [
                                            ['evaluate' => 'config.sources.postTypeOrderOptions'],
                                            ['evaluate' => 'config.sources.productOrderOptions'],
                                        ],
                                    ],
                                    'order_direction' => [
                                        'label' => trans('Direction'),
                                        'type' => 'select',
                                        'default' => 'DESC',
                                        'options' => [
                                            'Ascending' => 'ASC',
                                            'Descending' => 'DESC',
                                        ],
                                    ],
                                ],
                            ],

                            'order_alphanum' => [
                                'text' => trans('Alphanumeric Ordering'),
                                'type' => 'checkbox',
                            ],
                        ],
                        'directives' => [],
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::cross_sell_products',
                    ],
                ],
            ],
        ];
    }

    public static function sku($product)
    {
        return $product->get_sku();
    }

    public static function price($product)
    {
        $price = $product->get_price_html();

        if (empty($price)) {
            return;
        }

        return HtmlElement::tag(
            'div',
            [
                'class' => [
                    'tm-source-woo-price',
                    'tm-source-page' => Helper::isPageSource($product),
                    apply_filters('woocommerce_product_price_class', 'price'),
                ],
            ],
            $price
        );
    }

    public static function stock($product)
    {
        $stock = wc_get_stock_html($product);

        if (empty($stock)) {
            return;
        }

        return HtmlElement::tag(
            'div',
            [
                'class' => [
                    'tm-source-woo-stock',
                    'tm-source-page' => Helper::isPageSource($product),
                ],
            ],
            $stock
        );
    }

    public static function rating($product, $args)
    {
        $args += ['link' => true];
        $count = $product->get_rating_count();
        $average = $product->get_average_rating();

        if (wc_review_ratings_enabled() && $count > 0) {
            $rating = wc_get_rating_html($average, $count);

            if ($args['link'] && comments_open($product->get_id())) {
                $review_count = $product->get_review_count();
                $review_msg = _n(
                    '%s customer review',
                    '%s customer reviews',
                    $review_count,
                    'woocommerce'
                );
                $review = sprintf(
                    $review_msg,
                    '<span class="count">' . esc_html($review_count) . '</span>'
                );

                $link = '#reviews';
                if (!Helper::isPageSource($product)) {
                    $link = get_permalink($product->get_id()) . $link;
                }

                $rating .= sprintf(
                    ' <a href="%s" class="woocommerce-review-link" rel="nofollow">(%s)</a>',
                    $link,
                    $review
                );
            }

            if (empty($rating)) {
                return;
            }

            return HtmlElement::tag(
                'div',
                [
                    'class' => [
                        'tm-source-woo-rating',
                        'tm-source-page' => Helper::isPageSource($product),
                    ],
                ],
                $rating
            );
        }
    }

    public static function is_on_sale($product)
    {
        return $product->is_on_sale();
    }

    public static function add_to_cart_url($product)
    {
        return $product->add_to_cart_url();
    }

    public static function add_to_cart_text($product)
    {
        return $product->add_to_cart_text();
    }

    public static function additional_information($product)
    {
        return Helper::renderTemplate('do_action', [
            'woocommerce_product_additional_information',
            $product,
        ]) ?:
            null;
    }

    /**
     * @see wc_display_product_attributes
     *
     * @param mixed $product
     */
    public static function attributes($product)
    {
        $attributes = [];

        // Display weight and dimensions before attribute list.
        if (
            apply_filters(
                'wc_product_enable_dimensions_display',
                $product->has_weight() || $product->has_dimensions()
            )
        ) {
            if ($product->has_weight()) {
                $attributes['weight'] = [
                    'name' => __('Weight', 'woocommerce'),
                    'value' => wc_format_weight($product->get_weight()),
                ];
            }

            if ($product->has_dimensions()) {
                $attributes['dimensions'] = [
                    'name' => __('Dimensions', 'woocommerce'),
                    'value' => wc_format_dimensions($product->get_dimensions(false)),
                ];
            }
        }

        return $attributes +
            array_filter($product->get_attributes(), 'wc_attributes_array_filter_visible');
    }

    public static function galleryImageIds($product)
    {
        return $product->get_gallery_image_ids();
    }

    public static function upsell_products($product, $args)
    {
        $ids = $product->get_upsell_ids();

        if (empty($ids)) {
            return;
        }

        return CustomPostQueryType::resolvePosts(
            null,
            $args + [
                'post_type' => 'product',
                'include' => $ids,
            ]
        );
    }

    public static function cross_sell_products($product, $args)
    {
        $ids = $product->get_cross_sell_ids();

        if (empty($ids)) {
            return;
        }

        return CustomPostQueryType::resolvePosts(
            null,
            $args + [
                'post_type' => 'product',
                'include' => $ids,
            ]
        );
    }
}
