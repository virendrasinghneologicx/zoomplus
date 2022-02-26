<?php

namespace YOOtheme\Builder\Wordpress\Woocommerce;

use YOOtheme\Builder\Wordpress\Source\SourceListener as Listener;
use YOOtheme\Builder\Wordpress\Source\Type\TaxonomyQueryType;
use YOOtheme\Builder\Wordpress\Source\Type\TaxonomyType;
use YOOtheme\Config;
use YOOtheme\Str;

class SourceListener
{
    public static function initSource($source)
    {
        $source->objectType('Product', Type\ProductType::config());
        $source->objectType('ProductCat', Type\ProductCategoryType::config());
        $source->objectType('ProductsQuery', Type\CustomProductQueryType::config());
        $source->objectType('AttributeField', Type\AttributeFieldType::config());
        $source->objectType('WoocommerceFields', Type\FieldsType::config());

        static::renameLabel($source, 'ProductTagsQuery', 'customProductTag', 'Custom Product tag');
        static::renameLabel(
            $source,
            'ProductCatsQuery',
            'customProductCat',
            'Custom Product category'
        );

        foreach (static::getAttributeTaxonomies() as $taxonomy) {
            $source->queryType(TaxonomyQueryType::config($source, $taxonomy, false));
            $source->objectType(
                Str::camelCase($taxonomy->name, true),
                TaxonomyType::config($taxonomy)
            );
        }
    }

    public static function renameLabel($source, $name, $field, $label)
    {
        $source->objectType($name, [
            'fields' => [
                $field => [
                    'metadata' => compact('label'),
                ],
            ],
        ]);
    }

    public static function filterTaxonomy(array $taxonomies, $object)
    {
        $visibility = 'product_visibility';

        if ($object === 'product') {
            $taxonomies[$visibility] = get_taxonomy($visibility);
        }

        return $taxonomies;
    }

    public static function initCustomizer(Config $config)
    {
        $taxonomy = get_taxonomy('product_visibility');

        $terms = get_terms([
            'taxonomy' => $taxonomy->name,
            'hide_empty' => false,
        ]);

        $mapping = [
            'featured' => __('Featured', 'woocommerce'),
            'outofstock' => __('Out of Stock', 'woocommerce'),
            'rated-1' => sprintf(__('Rated %s out of 5', 'woocommerce'), 1),
            'rated-2' => sprintf(__('Rated %s out of 5', 'woocommerce'), 2),
            'rated-3' => sprintf(__('Rated %s out of 5', 'woocommerce'), 3),
            'rated-4' => sprintf(__('Rated %s out of 5', 'woocommerce'), 4),
            'rated-5' => sprintf(__('Rated %s out of 5', 'woocommerce'), 5),
        ];

        $config->add('customizer.taxonomies', [
            'product_visibility' => [
                'label' => $taxonomy->label,
                'options' => array_values(
                    array_filter(
                        array_map(function ($term) use ($mapping) {
                            if (isset($mapping[$term->name])) {
                                return ['value' => $term->term_id, 'text' => $mapping[$term->name]];
                            }
                        }, $terms)
                    )
                ),
            ],
        ]);

        $config->add('customizer.templates', [
            'taxonomy-product_cat' => [
                'label' => 'Product Category Archive',
            ],

            'taxonomy-product_tag' => [
                'label' => 'Product Tag Archive',
            ],
        ]);

        foreach (static::getAttributeTaxonomies() as $name => $taxonomy) {
            $config->add('customizer.templates', [
                "taxonomy-{$name}" => Listener::getTaxonomyArchive($taxonomy),
            ]);

            if ($terms = Listener::getTaxonomyTerms($taxonomy)) {
                $config->add("customizer.taxonomies.{$name}", [
                    'label' => $taxonomy->label,
                    'options' => $terms,
                ]);
            }
        }

        $config->update('customizer.sources.productOrderOptions', function ($options) {
            return array_merge((array) $options, [
                [
                    'label' => 'WooCommerce',
                    'options' => [
                        ['text' => __('Price', 'woocommerce'), 'value' => 'field:_price'],
                        [
                            'text' => __('Rating', 'woocommerce'),
                            'value' => 'field:_wc_average_rating',
                        ],
                        ['text' => __('Purchases', 'woocommerce'), 'value' => 'field:total_sales'],
                        ['text' => __('SKU', 'woocommerce'), 'value' => 'field:_sku'],
                    ],
                ],
            ]);
        });
    }

    public static function getAttributeTaxonomies()
    {
        $taxonomies = [];

        foreach (wc_get_attribute_taxonomy_names() as $name) {
            $taxonomy = get_taxonomy($name);

            if ($taxonomy && $taxonomy->public) {
                $taxonomies[$name] = $taxonomy;
            }
        }

        return $taxonomies;
    }
}
