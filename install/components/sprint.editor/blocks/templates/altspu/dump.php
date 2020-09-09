<? /** @var $block array */ ?>
<?
$filename = __DIR__.'/'.'my_'.$block['name'].'.php';
if (file_exists($filename)) {
    require $filename;
} else {
global $USER;
if ($USER->IsAuthorized()) echo "<div class='alert alert-error'>#{$block['name']}<br>Здравствуйте, пожалуйста сообщите об этой ошибке в Управление цифрового развития</div>";
}
?>
