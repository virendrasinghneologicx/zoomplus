<?php

foreach ($items as $item) {

    if ($item->type == 'header') {
        continue;
    }

    // Config
    $config->addAlias('~menuitem', "~theme.menu.items.{$item->id}");

    // List item

    $attrs = ['class' => [isset($item->class) ? $item->class : '']];

    if (isset($item->children)) {
        $attrs['class'][] = 'uk-parent';
    }

    if ($item->active) {
        $attrs['class'][] = 'uk-active';
    }

    // Title

    $title = $item->title;

    // Image

    $image = $config('~menuitem.image');
    $image_attrs['class'] = [
        $config('~menuitem.image-classes', ''),
    ];

    if ($view->isImage($image)) {
        $image = $view->image($image, $image_attrs + ['alt' => $item->title, 'uk-svg' => $view->isImage($image) == 'svg']);
    } elseif ($image) {
        $image = "<span {$this->attrs($image_attrs)} uk-icon=\"{$image}\"></span>";
    }

    if ($image && $config('~menuitem.image-only')) {
        $title = '';
    }

    // Title Suffix, e.g. cart quantity

    if ($suffix = $config('~menuitem.title-suffix')) {
        $title .= " {$config('~menuitem.title-suffix')}";
    }

    // Subtitle

    if ($subtitle = $config('~menuitem.subtitle')) {
        $subtitle = "<div>{$subtitle}</div>";
    }

    // Link

    $link = [];

    if (isset($item->url)) {
        $link['href'] = $item->url;
    }

    if (isset($item->target)) {
        $link['target'] = $item->target;
    }

    if (isset($item->anchor_title)) {
        $link['title'] = $item->anchor_title;
    }

    if (isset($item->anchor_rel)) {
        $link['rel'] = $item->anchor_rel;
    }

    if (isset($item->anchor_css)) {
        $link['class'] = $item->anchor_css;
    }

    echo "<li{$this->attrs($attrs)}><a{$this->attrs($link)}>{$image} {$title}{$subtitle}</a></li>";
}
