<?php

$image = $this->el('image', [

    'src' => $props['image'] ?: $props['hover_image'],
    'alt' => $props['image_alt'],
    'width' => $element['image_width'],
    'height' => $element['image_height'],
    'thumbnail' => [$element['image_width'], $element['image_height'], $element['image_orientation']],

]);

return $image;
