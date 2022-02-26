<?php
/**
 * The template for displaying a search form.
 */

namespace YOOtheme;

$view = app(View::class);

$result = get_view('~theme/templates/search', [

    'position' => get_current_sidebar(),
    'attrs' => [

        'id' => 'search-' . $view->uid(),
        'action' => home_url(),
        'method' => 'get',
        'role' => 'search',
        'class' => '',

    ],
    'fields' => [

        [
            'tag' => 'input',
            'name' => 's',
            'placeholder' => _x('Search', 'placeholder', 'yootheme'),
            'value' => get_search_query(),
            'required' => true,
        ],

    ],

]);

if ($echo) {
    echo $result;
} else {
    return $result;
}
