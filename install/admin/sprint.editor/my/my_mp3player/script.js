sprint_editor.registerBlock('my_mp3player', function($, $el, data) {
   var $player; //Плеер

   data = $.extend({
       mp3: ''
   }, data);

   this.getData = function() {
       return data;
   };

   this.collectData = function() {
       data.mp3 = $el.find('audio').attr('src');
       return data;
   };

   this.afterRender = function() {
       $el.prepend(sprint_editor.renderTemplate('my_box-yurgen_title', data));
       $el.find('input[type=file]').bindWithDelay('input', function() {
           if (typeof this.files === 'undefined') {
               return false;
           };
           if((this.files[0].size) > 41943040){
            alert('превышен размер файла(не более 40mb)');
           };
           ajax(this.files);
       }, 500);

       $el.find(".fl_inp").change(function() {
           var filename = $(this).val().replace(/.*\\/, "");
           $el.find(".fl_nm").html(filename);
       });
       if (data.mp3.length != 0) {
           $player = audiojs.create($el.find('audio'), {})[0];
       };

   };
   var ajax = function(files) {
       $el.find('.audio_box').hide(300);
       $el.find('.load').show(300);
       var data_f = new FormData();

       /* Перебираем выбранные файлы */
       $.each(files, function(key, value) {
           data_f.append(key, value);
       });
       data_f.append('my_mp3player', 1);
       var status_tag = $el.find('.status');
       $.ajax({
           url: sprint_editor.getBlockWebPath('my_mp3player') + '/download.php',
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
                   success(respond.files);
               }
               // error
               else {
                   status_tag.html('ОШИБКА: ' + respond.error);
               }
           },
           // функция ошибки ответа сервера
           error: function(jqXHR, status, errorThrown) {
               alert("Ошибка :" + jqXHR.responseText);
           }
       });
   };
   var success = function(file) {
       if (typeof $player == "undefined") {
           $player = audiojs.create($el.find('audio'), {})[0];
           $player.load(file[0]);
       } else {
           $player.load(file[0]);
       }
       $el.find('.track-details')
           .text(file[0].split('/').pop())
           .removeClass('error')
           .addClass('success');
       $el.find('.load').hide(300);
       $el.find('.audio_box').show(300);
   }
})