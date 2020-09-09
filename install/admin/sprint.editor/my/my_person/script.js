sprint_editor.registerBlock('my_person', function($, $el, data) {

   data = $.extend({
      array:{}
   }, data);

   this.getData = function() {
       return data;
   };
   this.collectData = function() {
      var trimed = [];
      $el.find('li').each(function (index, element) {
         var array1 = {};
         array1['text'] = $(element).find('span').html();
         array1['img'] = $(element).find('img').attr('src');
         console.log(array1);
         data.array[index] = array1;
     });
       return data;
   };

   this.afterRender = function() {
      $el.prepend(sprint_editor.renderTemplate('my_box-yurgen_title', data));
      $el.find('ul').sortable();
      $el.on('click', '.sp-toggle', function () {
         if ($el.hasClass('sp-show')) {
             $el.find('.sp-source').hide(250);
             $el.removeClass('sp-show');
         } else {
             $el.find('.sp-source').show(250);
             $el.addClass('sp-show');
         }
     });
      trumbowyg('.sp-text');
      var files; // переменная. будет содержать данные файлов

      // заполняем переменную данными файлов, при изменении значения file поля
      $el.on('change', 'input[type=file]', function() {
          files = this.files;
      });
      
      $el.on('click', 'input[type="submit"]', function (event) {
         event.preventDefault();
         if(
            ($el.find('input[type="file"]').val() == '')
             && 
            ($el.find('textarea').html() == '')
         ){
            alert('проверьте данные');
         }else{
            ajaxfile(files);
         };
         console.log($el.find('input[type="file"]').val());
         //ajaxfile();
     });
   };


   var ajaxfile  = function (files) {

      if (typeof files == 'undefined') return;
      var data_f = new FormData();
      $.each(files, function(key, value) {
          data_f.append(key, value);
      });
      data_f.append('my_file_upload', 1);

      $.ajax({
         url: sprint_editor.getBlockWebPath('my_person') + '/download.php',
         type: 'POST',
         data: data_f,
         cache: false,
         dataType: 'json',
         // отключаем обработку передаваемых данных, пусть передаются как есть
         processData: false,
         // отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
         contentType: false,
         // функция успешного ответа сервера
         success: function(respond, status, jqXHR) {
             // ОК
             if (typeof respond.error === 'undefined') {
               var files_path = respond.files;
               var html = '';
               html = files_path['0'];
               console.log(respond.files);
               $el.find('ul').append(
                  `<li>
                  <img src="`+html+`">
                  <span>`+mojo($el.find('.sp-text'))+`</span>
                  </li>`
               );
             }
             // error
             else {
                 console.log('ОШИБКА: ' + respond.error);
             }
         },
         // функция ошибки ответа сервера
         error: function(jqXHR, status, errorThrown) {
             alert("Ошибка :" + jqXHR.responseText);
         }

     });
   }
   var trumbowyg = function (el) {
      $el.find(el).trumbowyg({
         lang: 'ru',
         resetCss: true, //стиль страницы  НЕ влиял на внешний вид текста в редакторе
         defaultLinkTarget: '_blank', //Разрешить устанавливать целевое значение атрибута ссылки
         minimalLinks: true, //Уменьшите наложение ссылок, чтобы использовать только поля urlи text
         tagsToRemove: ['script', 'link', 'iframe', 'input','br','script'], //очистить код, удалив все теги, которые вы хотите
         removeformatPasted: true, //чтобы стили Не вставлялись из буфера обмена
         semantic: true, //Создает лучший, более семантически ориентированный HTML
         changeActiveDropdownIcon: true, // выпадающее меню изменится на значок активной подкнопки
         btns: [ //выбирать кнопки, отображаемые на панели кнопок
             ['strong', 'em', 'del'],
             ['fullscreen'],
         ]
     });
   }
   var mojo = function (tag) {
      var $text = $(tag).trumbowyg('html');
      console.log($text);

      /*убираем span появляющиеся из-за word*/
      $text = $text.replace(/<span.+?>.+?>/gi, '');

      /* убираем пустые теги   */
      $text = $text.replace(/<[^\/>][^>]*><\/[^>]+>/g, '');
      return $text;
   }
})