<?php

$image = $this->el('image', [

    'src' => $props['image'] ?: $props['hover_image'],
    'alt' => $props['image_alt'],
    'width' => $props['image_width'],
    'height' => $props['image_height'],
    'thumbnail' => [$props['image_width'], $props['image_height']],

]);

return $image;
