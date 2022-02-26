<?php

foreach ($items as $item) {

    // Config
    $config->addAlias('~menuitem', "~theme.menu.items.{$item->id}");

    // Children

    $children = isset($item->children);
    $indention = str_pad("\n", $level + 1, "\t");

    // List item

    $attrs = ['class' => [isset($item->class) ? $item->class : '']];

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

    // Header
    if ($item->type == 'header') {

        $title = "{$image} {$title}";

        // Divider
        if ($item->divider && !$children) {
            $title = '';
            $attrs['class'][] = 'uk-nav-divider';
        } elseif ($config('~menu.accordion') && $children) {
            $title = "<a href>{$title}</a>";
            if ($level === 1) {
                $attrs['class'][] = 'js-accordion';
            }
        } else {
            $attrs['class'][] = 'uk-nav-header';
        }

    // Link
    } else {

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

        $title = "<a{$this->attrs($link)}>{$image} {$title}</a>";
    }

    if ($children) {

        $attrs['class'][] = 'uk-parent';

        $children = ['class' => []];

        if ($level == 1) {
            $children['class'][] = 'uk-nav-sub';
        }

        $children = "{$indention}<ul{$this->attrs($children)}>\n{$this->self(['items' => $item->children, 'level' => $level + 1])}</ul>";
    }

    echo "{$indention}<li{$this->attrs($attrs)}>{$title}{$children}</li>";
}
