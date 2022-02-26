<?php

namespace YOOtheme\Builder\Wordpress\Woocommerce;

use WooCommerce;
use YOOtheme\View;

class TemplateListener
{
    public static function onTemplateInclude(View $view, $template)
    {
        if (
            !is_woocommerce() ||
            !$view['sections']->exists('builder') ||
            (!is_shop() && !is_product())
        ) {
            return $template;
        }

        /**
         * Hook: woocommerce_before_main_content.
         *
         * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
         * @hooked woocommerce_breadcrumb - 20
         * @hooked WC_Structured_Data::generate_website_data() - 30
         */
        remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

        /**
         * Hook: woocommerce_after_main_content.
         *
         * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        remove_action(
            'woocommerce_after_main_content',
            'woocommerce_output_content_wrapper_end',
            10
        );

        /**
         * Hook: woocommerce_single_product_summary.
         *
         * @hooked WC_Structured_Data::generate_product_data() - 60
         */
        remove_action(
            'woocommerce_single_product_summary',
            [WooCommerce::instance()->structured_data, 'generate_product_data'],
            60
        );

        $view['sections']->add('builder', function ($result) {
            $content[] = Helper::renderTemplate(function () {
                do_action('woocommerce_before_main_content');
            });

            $content[] = $result;

            $content[] = Helper::renderTemplate(function () {
                do_action('woocommerce_after_main_content');
            });

            if (is_product()) {
                WooCommerce::instance()->structured_data->generate_product_data();
            }

            return implode("\n", $content);
        });

        return $template;
    }
}
