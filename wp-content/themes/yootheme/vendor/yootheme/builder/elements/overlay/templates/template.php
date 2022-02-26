<?php

// Resets
if ($props['overlay_link']) { $props['title_link'] = ''; }

// New logic shortcuts
$props['overlay_cover'] = $props['overlay_style'] && $props['overlay_mode'] == 'cover';

$el = $this->el('div', [

    // Needs to be parent of `uk-link-toggle`
    'class' => [
        'uk-{text_color}' => !$props['overlay_style'] || $props['overlay_cover'],
    ],

]);

// Container
$container = $this->el($isLink = $props['link'] && $props['overlay_link'] ? 'a' : 'div', [

    'class' => [
        'el-container',

        'uk-box-shadow-{image_box_shadow}',
        'uk-box-shadow-hover-{image_hover_box_shadow}',

        'uk-box-shadow-bottom {@image_box_decoration: shadow}',
        'tm-mask-default {@image_box_decoration: mask}',
        'tm-box-decoration-{image_box_decoration: default|primary|secondary}',
        'tm-box-decoration-inverse {@image_box_decoration_inverse} {@image_box_decoration: default|primary|secondary}',
        'uk-inline {@!image_box_decoration: |shadow}',
        'uk-inline-clip {@!image_box_decoration}',

        'uk-transition-toggle' => $isTransition = $props['overlay_hover'] || $props['image_transition'] || $props['hover_image'],

    ],

    'style' => [
        'min-height: {image_min_height}px; {@!image_box_decoration}',
        'background-color: ' . $props['media_background'] . ';' => $props['media_background'],
    ],

    'tabindex' => $isTransition && !$isLink ? 0 : null,

    // Inverse text color on hover
    'uk-toggle' => $props['text_color_hover'] && ((!$props['overlay_style'] && $props['hover_image']) || ($props['overlay_cover'] && $props['overlay_hover'] && $props['overlay_transition_background']))
        ? 'cls: uk-light uk-dark; mode: hover; target: !*'
        : false,
]);

$box_decoration_clip = $this->el('div', [

    'class' => [
        'uk-inline-clip',
    ],

    'style' => [
        'min-height: {image_min_height}px; {@image_box_decoration}',
    ],

]);

$overlay = $this->el('div', [

    'class' => [
        'uk-{overlay_style}',
        'uk-transition-{overlay_transition} {@overlay_hover}',

        'el-overlay uk-position-cover {@overlay_cover}',
        'uk-position-{overlay_margin} {@overlay_cover}',
    ],

]);

$position = $this->el('div', [

    'class' => [
        'uk-position-{overlay_position} [uk-position-{overlay_margin} {@overlay_style}]',
        'uk-transition-{overlay_transition} {@overlay_hover}' => !($props['overlay_transition_background'] && $props['overlay_cover']),
    ],

]);

$content = $this->el('div', [

    'class' => [
        $props['overlay_style'] ? 'uk-overlay' : 'uk-panel',
        'uk-padding {@!overlay_padding} {@!overlay_style}',
        'uk-padding-{!overlay_padding: |none}',
        'uk-padding-remove {@overlay_padding: none} {@overlay_style}',
        'uk-width-{overlay_maxwidth} {@!overlay_position: top|bottom}',
        'uk-margin-remove-first-child',
    ],

]);

if (!$props['overlay_cover']) {
    $position->attr($overlay->attrs);
}

// Link
$link = include "{$__dir}/template-link.php";

?>

<?= $el($props, $attrs) ?>
    <?= $container($props) ?>

        <?php if ($props['image_box_decoration']) : ?>
        <?= $box_decoration_clip($props) ?>
        <?php endif ?>

            <?= $this->render("{$__dir}/template-media", compact('props')) ?>

            <?php if ($props['media_overlay']) : ?>
            <div class="uk-position-cover" style="background-color:<?= $props['media_overlay'] ?>"></div>
            <?php endif ?>

            <?php if ($props['overlay_cover']) : ?>
            <?= $overlay($props, '') ?>
            <?php endif ?>

            <?php if ($props['title'] || $props['meta'] || $props['content'] || ($props['link'] && $props['link_text'])) : ?>
            <?= $position($props, $content($props, $this->render("{$__dir}/template-content", compact('props', 'link')))) ?>
            <?php endif ?>

        <?php if ($props['image_box_decoration']) : ?>
        <?= $box_decoration_clip->end() ?>
        <?php endif ?>

    <?= $container->end() ?>
</div>
