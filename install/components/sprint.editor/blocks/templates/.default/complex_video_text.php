<?php /** @var $block array */?>
<div class="sp-video-text">
    <?php
    $this->includeBlock(array_merge($block['video'], ['name' => 'video']));
    $this->includeBlock(array_merge($block['text'], ['name' => 'text']));
    ?>
</div>
