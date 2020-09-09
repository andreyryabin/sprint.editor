
<?
$width = 800;
$images = Sprint\Editor\Blocks\Gallery::getImages(
    $block['slider'], [
    'width'  => $width,
    'height' => $width * 0.6,
    'exact'  => 1,
]
);
$ID = uniqid();
?>

<script src="/bitrix/admin/sprint.editor/assets/my_slider/siema.min.js"></script>
    <div
    class="siema"
    data-selector="<?=$ID?>"
    <?if ( $block['OPTIONS']['max_width'] !=0):?>style="max-width:<?=$block['OPTIONS']['max_width']?>%"<?endif;?>>
        <?foreach ($images as $key => $value):?>
            <div class="siema_wrapperBlock">
                <img src="<?=$value['SRC']?>" alt="">
                <?if($value['DESCRIPTION']):?>
                    <p class="block_info"><?=$value['DESCRIPTION']?></p>
                <?endif;?>
            </div>
        <?endforeach?>
    </div>




<script>
    document.addEventListener("DOMContentLoaded", ()=>{
        var selector = document.querySelector("div[data-selector='<?=$ID?>']");
        new Siema({
            selector: selector,
        });
    });
</script>
<style>
    .siema{
        max-width: 800px;
    }
    .siema_wrapperBlock{
        position: relative;
        padding-top: 62.5%;
    }
    .siema img{
        top: 0;
        left: 0;
        position: absolute;
        display: block;
        width:100%;
    }
    .siema .block_info{
        position: absolute;
        z-index: 1;
        font-weight: bold;
        bottom: 10%;
        left: 2%;
        background-color: rgba(230, 239, 246, 0.8);
        width: 70%;
        color: #157FC4;
        padding: 20px;
        transition: opacity 1.5s 0s linear;
    }
    @media (max-width: 768px) {
        .siema .block_info{
            display: block;
            position: inherit;
            width: 100%;
            top: 100%;
            left: 0;
            margin:0;
            padding: 10px;
        }
    }

</style>