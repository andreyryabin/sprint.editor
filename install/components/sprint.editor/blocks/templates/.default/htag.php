<?php
/** @var $block array */

$tag = in_array($block['type'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $block['type'] : 'h2';

$anc = !empty($block['anchor']) ? '<a name="' . htmlspecialcharsbx($block['anchor']) . '"></a>' : '';

$val = htmlspecialcharsbx((string)$block['value']);

echo "$anc<$tag>$val</$tag>";