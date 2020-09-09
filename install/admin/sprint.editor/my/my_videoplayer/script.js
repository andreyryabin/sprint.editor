sprint_editor.registerBlock('my_videoplayer', function ($, $el, data) {

    data = $.extend({
        url: ''
    }, data);

    this.getData = function () {
      
        return data;
    };


    this.collectData = function () {
         data.url = $el.find('video').attr('src');
        return data;
    };

    this.afterRender = function () {
      $el.prepend(sprint_editor.renderTemplate('my_box-yurgen_title', data));
      var $STATUS = $el.find('.status');
      $STATUS.html('');
      $el.on('change', '.sp-url', function(event) {
         console.log("ищу");
         $.ajax({
            url: $el.find('.sp-url').val(),
            type:'HEAD',
            error: function()
            {
                $STATUS.html('<span style="color:red">Не найдено!</span>');
            },
            success: function()
            {
               $el.find('video').attr({'src':$el.find('.sp-url').val()}) ;
               $STATUS.html('<span style="color:green">Найдено!</span>');
            }
        });
      });
      //$el.find('.sp-preview');
   }
});