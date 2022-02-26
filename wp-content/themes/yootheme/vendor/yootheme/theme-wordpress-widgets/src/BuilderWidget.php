<?php

use function YOOtheme\app;
use YOOtheme\Builder;

class BuilderWidget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct('', 'Builder', [
            'description' => 'A Layout Builder for your site.',
            'settings' => ['title' => '', 'content' => '{}'],
        ]);
    }

    public function widget($args, $instance)
    {
        $output = [$args['before_widget']];
        $settings = array_merge($this->widget_options['settings'], $instance);

        if ($settings['title']) {
            array_push($output, $args['before_title'], $settings['title'], $args['after_title']);
        }

        if (!$settings['content']) {
            $settings['content'] = '{}';
        } else {
            $output[] =
                '<div class="uk-text-danger">Builder only supported on "top" and "bottom"</div>';
        }

        array_push($output, "<!-- {$settings['content']} -->", $args['after_widget']);

        echo implode('', $output);
    }

    public function form($instance)
    {
        $builder = app(Builder::class);
        $settings = array_merge($this->widget_options['settings'], $instance);
        $content = json_encode($builder->load($settings['content']));
        ?>
        <p>
            <label for="<?= $this->get_field_id('title') ?>"><?= 'Title' ?>:</label>
            <input id="<?= $this->get_field_id(
                'title'
            ) ?>" class="input-title widefat" type="text" name="<?= $this->get_field_name('title') ?>" value="<?= esc_attr($settings['title']) ?>">
        </p>
        <p>
            <?php if (is_customize_preview()): ?>
            <button class="button button-builder" type="button"><?= 'Open Builder' ?></button>
            <?php else: ?>
            <?= __('Only available in Customizer.', 'yootheme') ?>
            <?php endif; ?>
            <input class="input-content" type="hidden" name="<?= $this->get_field_name(
                'content'
            ) ?>" value="<?= esc_attr($content) ?>" >
        </p>
    <?php return '';
    }
}
