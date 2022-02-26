<?php

use YOOtheme\Builder\Wordpress\Woocommerce\Hook;

$el = $this->el('div', [

    'class' => [
        'uk-panel tm-element-woo-add-to-cart',
    ],

]);

echo $el($props, $attrs);

/**
 * Hook: woocommerce_single_product_summary.
 *
 * @hooked woocommerce_template_single_add_to_cart - 30
 */
Hook::doAction('woocommerce_single_product_summary', ['start' => 30, 'end' => 39]);

echo $el->end();
