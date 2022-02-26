<?php

$hover_image = $this->el('image', [

    'class' => [
        'el-hover-image',
        'uk-transition-{image_transition}',
        'uk-transition-fade {@!image_transition}',
    ],

    'src' => $props['hover_image'],
    'alt' => true,
    'width' => $element['image_width'],
    'height' => $element['image_height'],
    'uk-img' => 'target: !.uk-slider-items',
    'thumbnail' => true,
    'uk-cover' => true,

]);

return $hover_image;
