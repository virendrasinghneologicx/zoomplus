<?php

$el = $this->el('div');

echo $el($props, $attrs);
comments_template('/vendor/yootheme/builder-wordpress-source/elements/comments/templates/comments.php');
echo $el->end();
