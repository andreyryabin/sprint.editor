<?/** @var $block array */?><table class="c-properties">
    <?foreach ($block['elements'] as $item):?>
        <tr>
            <td class="c-properties_title"><?=$item['title']?></td>
            <td class="c-properties_text"><?=$item['text']?></td>
        </tr>
    <?endforeach;?>
</table>