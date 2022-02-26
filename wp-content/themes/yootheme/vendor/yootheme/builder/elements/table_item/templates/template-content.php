<?php

namespace YOOtheme;

if (!Str::length($props['content'])) {
    return;
}

// Content
$el = $this->el('div', [

    'class' => [
        'el-content uk-panel',
        'uk-text-{content_style: meta|lead}',
        'uk-{content_style: h1|h2|h3|h4|h5|h6} uk-margin-remove',
    ],

]);

echo $el($element, $props['content']);
