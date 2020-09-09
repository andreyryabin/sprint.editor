<ul class="my_person big-list">
   <?foreach ($block['array'] as $key => $value):?>
         <li>
            <img src="<?=$value['img']?>" alt="">
            <?=$value['text']?>
         </li>
   <?endforeach?>
</ul>