<?php
/** @global $APPLICATION CMain */
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_TRASH_FILES'));

\Bitrix\Main\UI\Extension::load("ajax");
\Bitrix\Main\UI\Extension::load("ui.progressbar");
\Bitrix\Main\UI\Extension::load("ui.notification");
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.alerts");

?>
<style>
    .sp-content-row {
        margin-bottom: 15px;
        /*background: #fff;*/
        width: 80%;
        /*padding: 16px;*/
    }

    .sp-alert-danger {
        padding: 16px 34px 16px 18px;
        color: #e92f2a;
        background: #ffe8e8;
        /*white-space: pre;*/
        margin-bottom: 10px;
    }

    .sp-alert-primary {
        padding: 16px 34px 16px 18px;
        color: #008dba;
        background: #e5f9ff;
        margin-bottom: 10px;
    }

</style>

<div class="sp-content-row">
    <button id="sprint_start" class="ui-btn ui-btn-primary">Начать поиск</button>
    <button id="sprint_stop" class="ui-btn" disabled="disabled">Остановить</button>
</div>

<div class="sp-content-row" id="sprint_messages"></div>

<script type="text/javascript">

    BX.ready(function () {
        let $sprint_messages = BX('sprint_messages');
        let $sprint_start = BX('sprint_start')
        let $sprint_stop = BX('sprint_stop')
        let START_PROCESS = false;

        let CONTROLLER = 'sprint:editor.controller.cleaner';
        let NEXT_ACTION = '';

        BX.bind($sprint_start, 'click', function () {
            start();
        });
        BX.bind($sprint_stop, 'click', function () {
            stop();
        });

        function start() {
            BX.addClass($sprint_start, 'ui-btn-wait');
            BX.adjust($sprint_start, {props: {disabled: true}});
            BX.adjust($sprint_stop, {props: {disabled: false}});

            START_PROCESS = true;

            BX.cleanNode($sprint_messages);

            runProcess({next_action: 'start'});
        }

        function stop() {
            BX.removeClass($sprint_start, 'ui-btn-wait');
            BX.adjust($sprint_start, {props: {disabled: false}});
            BX.adjust($sprint_stop, {props: {disabled: true}});

            START_PROCESS = false;
        }

        function runProcess(fields) {
            showProcess(fields);

            if (!START_PROCESS) {
                return;
            }

            if (!fields.next_action) {
                stop();
                return;
            }

            if (fields.next_action !== NEXT_ACTION) {
                NEXT_ACTION = fields.next_action;
                fields = {hello: 'w'}
            }


            BX.ajax.runAction(CONTROLLER + '.' + NEXT_ACTION, {
                data: {fields: fields},
            }).then(
                function (response) {
                    if (response.data) {
                        runProcess(response.data);
                        return;
                    }
                    stop();
                },
                function (response) {
                    stop();
                    showErrors(response.errors);
                }
            );
        }


        function showProcess(fields) {
            if (fields.messages && fields.messages.length > 0) {
                fields.messages.forEach(item => showMessage(item))
                delete fields.messages;
            }
        }

        function showMessage(message) {
            let css = (message.error) ? 'sp-alert-danger' : 'sp-alert-primary';
            if (message.id) {
                if (BX(message.id)) {
                    BX.adjust(BX(message.id), {
                        html: message.text
                    });
                    return;
                }

                BX.append(BX.create("div", {
                    props: {className: css, id: message.id},
                    html: message.text
                }), $sprint_messages);
                return;
            }

            BX.append(BX.create("div", {
                props: {className: css},
                html: message.text
            }), $sprint_messages);
        }

        function showErrors(errors) {
            if (errors && errors.length > 0) {
                errors.forEach(item => showMessage({
                    text: item.message,
                    error: true
                }))
            }
        }

        function createProgressBar(id, value, maxValue) {
            var bar = new BX.UI.ProgressBar({
                size: BX.UI.ProgressBar.Size.LARGE,
                color: BX.UI.ProgressBar.Color.PRIMARY,
                statusType: BX.UI.ProgressBar.Status.COUNTER,
                maxValue: maxValue,
                value: value,
                textBefore: "Выполняется поиск",
            });
            bar.update(123);
            BX.append(bar.getContainer(), $sprint_messages);
        }

    });

</script>
