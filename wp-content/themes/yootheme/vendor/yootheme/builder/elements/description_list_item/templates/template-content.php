<?php

namespace YOOtheme;

if (!Str::length($props['content'])) {
    return;
}

// Content
$content = $this->el('div', [

    'class' => [
        'el-content uk-panel',
        'uk-text-{content_style: meta|lead}',
        'uk-{content_style: h1|h2|h3|h4|h5|h6} uk-margin-remove',
    ],

]);

// Link
$link = $this->el('a', [
    'class' => [
        'uk-link-{0}' => $element['link_style'],
        'uk-margin-remove-last-child',
    ],
    'href' => ['{link}'],
    'target' => ['_blank {@link_target}'],
    'uk-scroll' => str_starts_with((string) $props['link'], '#'),
]);

echo $content($element, $props['link'] ? $link($props, $props['content']) : $props['content']);
