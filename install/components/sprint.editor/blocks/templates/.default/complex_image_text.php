<?php /** @var $block array */ ?>
<div class="sp-image-text">
    <?php
    $this->includeBlock(array_merge($block['image'], ['name' => 'image']));
    $this->includeBlock(array_merge($block['text'], ['name' => 'text']));
    ?>
</div>
