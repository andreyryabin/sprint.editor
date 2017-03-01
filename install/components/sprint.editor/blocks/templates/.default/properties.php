<?/** @var $block array */?><table class="c-properties" style="width: 400px;border-collapse: collapse;">
    <?foreach ($block['elements'] as $item):?>
        <tr>
            <td style="border: 1px solid #ccc;padding: 0 5px;font-weight: bold"><?=$item['title']?></td>
            <td style="border: 1px solid #ccc;padding: 0 5px;"><?=$item['text']?></td>
        </tr>
    <?endforeach;?>
</table>