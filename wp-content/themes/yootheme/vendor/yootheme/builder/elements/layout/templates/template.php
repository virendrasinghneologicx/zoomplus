<?php

if (isset($prefix)) {
    echo "<!-- Builder #{$prefix} -->";
}

// Add elements inline css above the content to ensure css is present when rendered
if (!empty($props['css'])) {
    $css = preg_replace('/[\r\n\t\h]+/u', ' ', $props['css']);
    echo "<style>{$css}</style>";
}

echo $builder->render($children);
