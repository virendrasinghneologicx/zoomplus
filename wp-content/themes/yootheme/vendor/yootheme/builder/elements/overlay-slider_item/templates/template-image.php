<?php

$image = $this->el('image', [

    'src' => $props['image'] ?: $props['hover_image'],
    'alt' => $props['image_alt'],
    'width' => $element['image_width'],
    'height' => $element['image_height'],
    'uk-img' => 'target: !.uk-slider-items',
    'thumbnail' => true,

]);

return $image;
