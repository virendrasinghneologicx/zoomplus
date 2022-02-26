<?php

namespace YOOtheme\Theme\Wordpress;

use YOOtheme\Path;
use YOOtheme\Url;

class EditorListener
{
    /**
     * Enqueue classic editor.
     */
    public static function enqueueEditor()
    {
        if (is_callable('wp_enqueue_editor')) {
            wp_enqueue_editor();
            return;
        }

        /**
         * WordPress 4.7 and earlier. Can be removed when not needed anymore.
         * If removed: Will fail gracefully and fall back to code editor only.
         */
        wp_enqueue_script('utils');
        wp_enqueue_script('wplink');

        // create dummy editor to initialize tinyMCE
        echo '<div style="display:none">';
        wp_editor('', 'yo-editor-dummy', [
            'wpautop' => false,
            'media_buttons' => true,
            'textarea_name' => 'textarea-dummy-yootheme',
            'textarea_rows' => 10,
            'editor_class' => 'horizontal',
            'teeny' => false,
            'dfw' => false,
            'tinymce' => true,
            'quicktags' => true,
        ]);
        echo '</div>';
    }

    public static function enqueueEditorJs()
    {
        wp_enqueue_script('yoo-editor', Url::to(Path::get('../app/editor.min.js'), [], is_ssl()));
        wp_print_scripts('yoo-editor');
    }
}
