<?php /** @var $block array */ ?>
<div class="sp-video-text">
    <?php

    echo "<pre>";print_r($block);/*debug*/echo "</pre>";
    $this->includeBlock(array_merge($block['video'], ['name' => 'video']));
    $this->includeBlock(array_merge($block['text'], ['name' => 'text']));
    ?>
</div>
