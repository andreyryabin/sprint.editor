<?php /** @var $block array */

use Sprint\Editor\Module;

$file = Module::getSnippetsDir() . $block['file'];

if ($block['file'] && is_file($file)) {
    include $file;
}
