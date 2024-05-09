document.addEventListener("DOMContentLoaded", function (e) {

    var info = {
        description: [
            'На этой странице можно поддержать улучшения, предложенные пользователями редактора, которые вы хотели бы видеть в обновлениях.' +
            '<br>' +
            'Именно благодаря активности участников в редакторе появилось большинство существующих блоков, настроек и возможностей.' +
            '<br>' +
            'Примеры улучшений на вкладке <a href="https://marketplace.1c-bitrix.ru/solutions/sprint.editor/#tab-log-link" target="_blank">Что нового</a>' +
            '<br><br>' +
            'Предложить свою идею по улучшению можно в <a href="https://t.me/sprint_editor" target="_blank">telegram-группе</a>' +
            'или на вкладке <a href="https://marketplace.1c-bitrix.ru/solutions/sprint.editor/#tab-comments-link" target="_blank">Обсуждения</a>' +
            '<br>' +
            'Появление вашей идеи в списке ниже означает, что она реализуема, полезна и взята в работу.' +
            '<br>' +
            'После завершения сбора она появится в ближайшем обновлении модуля.'
        ],
        items: [
            {
                title: "Отдельный раздел для статей",
                description: "Разработка в админке отдельного раздела со статьями, новый тип свойства у инфоблоков \"привязка к статье\".",
                content: "<iframe src=\"https://yoomoney.ru/quickpay/fundraise/widget?billNumber=3Nh3BQRLQqg.231129\" width=\"500\" height=\"480\" frameborder=\"0\" allowtransparency=\"true\" scrolling=\"no\"></iframe>"
            },
            {
                title: "Улучшение конструктора блоков",
                description: "Удаление блоков, перемещение шаблонов, создание настроек, именованные названия блоков, документация.",
                content: "<iframe src=\"https://yoomoney.ru/quickpay/fundraise/widget?billNumber=S6kHngOGM5A.231130&\" width=\"500\" height=\"480\" frameborder=\"0\" allowtransparency=\"true\" scrolling=\"no\"></iframe>"
            },
            {
                title: "Обновление модуля",
                description: "Поддержать ближайшее обновление модуля",
                content: "<iframe src=\"https://yoomoney.ru/quickpay/fundraise/widget?billNumber=12KAE0J04Q3.240509&\" width=\"500\" height=\"480\" frameborder=\"0\" allowtransparency=\"true\" scrolling=\"no\"></iframe>"
            }
        ]
    };

    let $el = document.getElementById('support_page');

    $el.innerHTML = render(info);

    events($el);


    function render(info) {
        let html = '';

        if (info.description) {
            html += '<div class="sp-support-description">' + info.description + '</div>';
        }

        if (info.items) {
            html += '<div class="sp-support-table">'
            html += '<div class="sp-support-row">'

            html += '<div class="sp-support-col-tabs">'
            info.items.forEach(function (item) {
                html += '<div class="sp-support-link">';
                html += '<strong>' + item.title + '</strong>';
                html += '<div>' + item.description + '</div>';
                html += '</div>';
            });
            html += '</div>'

            html += '<div class="sp-support-col-contents">'
            info.items.forEach(function (item) {
                html += '<div class="sp-support-content">' + item.content + '</div>';
            });
            html += '</div>'

            html += '</div>'
            html += '</div>'
        }

        return html;
    }

    function events($el) {
        let links = $el.getElementsByClassName('sp-support-link');
        let contents = $el.getElementsByClassName('sp-support-content');

        for (let index = 0; index < links.length; index++) {
            links[index].addEventListener('click', function () {
                open_tab(index);
            });
        }

        if (links.length > 0) {
            open_tab(0);
        }

        function open_tab(tabindex) {
            for (let index = 0; index < links.length; index++) {
                if (tabindex === index) {
                    links[index].classList.add('active');
                } else {
                    links[index].classList.remove('active');
                }
            }
            for (let index = 0; index < contents.length; index++) {
                if (tabindex === index) {
                    contents[index].style.display = 'block';
                } else {
                    contents[index].style.display = 'none';
                }
            }
        }
    }


});
