<? /**
 * @var $block array
 * @var $this \SprintEditorBlocksComponent
 */ ?>
<div class="questions-answers-list">
    <? foreach ($block['items'] as $item): ?>
        <div class="question-answer-wrap">
            <div class="question">
                <?= $item['title'] ?>
                <div class="open-answer"><span class="hide-answer-text">Скрыть</span><span class="open-answer-text">Подробнее</span>
                    <div class="open-answer-btn"></div>
                </div>
            </div>
            <div class="answer" style="display: none;">
                <? foreach ($item['blocks'] as $itemblock): ?>
                    <? $this->includeBlock($itemblock) ?>
                <? endforeach; ?>
            </div>
        </div>
    <? endforeach; ?>
</div>