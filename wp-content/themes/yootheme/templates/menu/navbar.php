<?php

use YOOtheme\Arr;

// Config
$config->addAlias('~navbar', '~theme.navbar');

foreach (array_values($items) as $i => $item) {

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

    // Show Icon only
    if ($image && $config('~menuitem.image-only')) {
        $title = '';
    }

    // Title Suffix, e.g. cart quantity

    if ($suffix = $config('~menuitem.title-suffix')) {
        $title .= " {$config('~menuitem.title-suffix')}";
    }

    // Link

    // Header
    if ($item->type === 'header') {

        if (!$children && $level == 1) {
            continue;
        }

        $title = "{$image} {$title}";

        if ($level > 1 && $item->divider && !$children) {
            $title = '';
            $attrs['class'][] = 'uk-nav-divider';
        } elseif ($children) {
            $link = [];
            if (isset($item->anchor_css)) {
                $link['class'] = $item->anchor_css;
            }
            $title = "<a{$this->attrs($link)}>{$title}</a>";
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

        // Subtitle
        if ($title && $subtitle = $level == 1 ? $config('~menuitem.subtitle') : '') {
            $title = "<div>{$title}<div class=\"uk-navbar-subtitle\">{$subtitle}</div></div>";
        }

        $title = "<a{$this->attrs($link)}>{$image} {$title}</a>";
    }

    if ($children) {

        $children = ['class' => []];
        $attrs['class'][] = 'uk-parent';

        if ($level == 1) {

            $children['class'][] = 'uk-navbar-dropdown';

            // Use `hover` instead of `hover, click` so dropdown can't be closed on click if in hover mode
            $mode = $item->type === 'header' ? ($config('~navbar.dropdown_click') ? 'click' : 'hover') : false;

            if (($justify = $config('~menuitem.justify')) || $mode) {

                $boundary = $justify || $config('~navbar.dropbar') && $config('~navbar.dropdown_boundary');

                $children['uk-drop'] = json_encode(array_filter([
                    'clsDrop' => 'uk-navbar-dropdown',
                    'flip' => 'x',
                    'pos' => $justify ? 'bottom-justify' : "bottom-{$config('~navbar.dropdown_align')}",
                    'boundary' => $boundary ? '.tm-header .uk-navbar-container' : false,
                    'boundaryAlign' => $boundary,
                    'mode' => $mode,
                    'container' => $config('~navbar.sticky') ? '.tm-header > [uk-sticky]' : '.tm-header',
                ]));
            }

            $columns = Arr::columns($item->children, $config('~menuitem.columns', 1));
            $columnsCount = count($columns);

            $wrapper = [
                'class' => [
                    'uk-navbar-dropdown-grid',
                    "uk-child-width-1-{$columnsCount}",
                ],
                'uk-grid' => true,
            ];

            if ($columnsCount > 1 && !$justify) {
                $children['class'][] = "uk-navbar-dropdown-width-{$columnsCount}";
            }

            $columnsStr = '';
            foreach ($columns as $column) {
                $columnsStr .= "<div><ul class=\"uk-nav uk-navbar-dropdown-nav\">\n{$this->self(['items' => $column, 'level' => $level + 1])}</ul></div>";
            }

            $children = "{$indention}<div{$this->attrs($children)}><div{$this->attrs($wrapper)}>{$columnsStr}</div></div>";

        } else {

            if ($level == 2) {
                $children['class'][] = 'uk-nav-sub';
            }

            $children = "{$indention}<ul{$this->attrs($children)}>\n{$this->self(['items' => $item->children, 'level' => $level + 1])}</ul>";
        }
    }

    echo "{$indention}<li{$this->attrs($attrs)}>{$title}{$children}</li>";
}
