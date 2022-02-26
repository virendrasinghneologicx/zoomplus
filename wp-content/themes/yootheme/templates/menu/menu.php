<?php

// Config
$config->addAlias('~mobile', '~theme.mobile');
$config->addAlias('~header', '~theme.header');
$config->addAlias('~navbar', '~theme.navbar');

// Menu ID
$attrs['id'] = $config('~menu.tag_id');

$hasHeaderParent = function ($items) {
    return array_filter($items, function ($item) {
        return $item->type == 'header' && !empty($item->children) && isset($item->url) && ($item->url === '#' || $item->url === '');
    });
};

// Menu type

// Temporary force menu_style to default if set to 'nav' on 'head' positions
if ($config('~menu.menu_style') == 'nav' && in_array($config('~menu.position'), ['header', 'navbar', 'navbar-split', 'toolbar-left', 'toolbar-right', 'logo', 'logo-mobile'])) {
    $config->set('~menu.menu_style', '');
}

if ($config('~menu.menu_style') == 'iconnav') {

    $type = 'iconnav';
    $attrs['class'][] = 'uk-iconnav';

} elseif ($config('~menu.menu_style') == 'subnav') {

    $type = 'subnav';
    $attrs['class'][] = 'uk-subnav';
    $attrs['class']['uk-subnav-divider'] = $config('~menu.menu_divider');

} elseif ($config('~menu.menu_style') == 'nav') {

    $type = 'nav';
    $attrs['class'][] = 'uk-nav uk-nav-default';
    $attrs['class']['uk-nav-divider'] = $config('~menu.menu_divider');

// Default on Navbar
} elseif (in_array($config('~menu.position'), ['navbar', 'navbar-split'])) {

    $layout = $config('~header.layout');

    if (preg_match('/^(offcanvas|modal)/', $layout)) {

        $type = 'nav';
        $attrs['class'][] = 'uk-nav';
        // Layout Header Settings
        $attrs['class'][] = "uk-nav-{$config('~navbar.toggle_menu_style')}";
        $attrs['class'][] = $config('~navbar.toggle_menu_divider') ? 'uk-nav-divider' : '';
        $attrs['class'][] = $config('~navbar.toggle_menu_center') ? 'uk-nav-center' : '';

    } else {

        $type = 'navbar';
        $attrs['class'][] = 'uk-navbar-nav';

    }

    if ($layout == 'stacked-center-split' && $config('~menu.split')) {

        $length = ceil(count($items) / 2);

        if ($config('~menu.position') == 'navbar-split') {
            $items = array_slice($items, 0, $length);
        } else {
            $items = array_slice($items, $length);
        }
    }

// Default on Header
} elseif (in_array($config('~menu.position'), ['header', 'header-split'])) {

    $layout = $config('~header.layout');

    if (preg_match('/^(stacked)/', $layout)) {

        $type = 'subnav';
        $attrs['class'][] = 'uk-subnav';

    // Render in navbar
    } else {

        $type = 'navbar';
        $attrs['class'][] = 'uk-navbar-nav';

    }

// Default on Toolbar and Logo
} elseif (in_array($config('~menu.position'), ['toolbar-left', 'toolbar-right', 'logo', 'logo-mobile'])) {

    $type = 'subnav';
    $attrs['class'][] = 'uk-subnav';

// Default on Mobile
} elseif ($config('~menu.position') == 'mobile') {

    $type = 'nav';
    $attrs['class'][] = 'uk-nav';
    // Layout Mobile Settings
    $attrs['class'][] = "uk-nav-{$config('~mobile.menu_style')}";
    $attrs['class'][] = $config('~mobile.menu_divider') ? 'uk-nav-divider' : '';
    $attrs['class'][] = $config('~mobile.menu_center') ? 'uk-nav-center' : '';

// Default on Sidebar, Top, Bottom, Builder 1-6
} else {

    $type = 'nav';
    $attrs['class'][] = 'uk-nav uk-nav-default';

}

// Accordion menu

if ($type == 'nav' && $hasHeaderParent($items)) {

    $config->set('~menu.accordion', true);
    $attrs['class'][] = 'uk-nav-parent-icon uk-nav-accordion';
    $attrs['uk-nav'] = 'targets: > .js-accordion';

}

?>

<ul<?= $this->attrs($attrs) ?>>
    <?= $view("~theme/templates/menu/{$type}", ['items' => $items, 'level' => 1]) ?>
</ul>
