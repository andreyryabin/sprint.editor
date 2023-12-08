function sprint_stepper(CONTROLLER) {

    let $sprint_container = BX('sprint_stepper');

    let $sprint_buttons = BX.create("div", {
        props: {className: 'sp-stepper-group'},
    });

    let $sprint_messages = BX.create("div", {
        props: {className: 'sp-stepper-group'},
    });

    let $sprint_start = BX.create("button", {
        props: {className: 'ui-btn ui-btn-primary'},
        html: 'Начать поиск'
    });

    let $sprint_stop = BX.create("button", {
        props: {className: 'ui-btn', disabled: true},
        html: 'Остановить'
    });

    BX.append($sprint_start, $sprint_buttons);
    BX.append($sprint_stop, $sprint_buttons);
    BX.append($sprint_buttons, $sprint_container);
    BX.append($sprint_messages, $sprint_container);

    let START_PROCESS = false;

    let NEXT_ACTION = '';

    let BUTTONS = [];

    BX.bind($sprint_start, 'click', function () {
        start();
    });
    BX.bind($sprint_stop, 'click', function () {
        stop();
    });

    function addButton(button) {
        let $button = BX.create("button", {
            props: {className: 'ui-btn ui-btn-' + button.color},
            html: button.text,
        });

        BUTTONS.push($button);

        BX.append($button, $sprint_buttons);

        BX.bind($button, 'click', function () {
            BX.addClass($button, 'ui-btn-wait');
            BX.adjust($button, {props: {disabled: true}});

            BX.adjust($sprint_start, {props: {disabled: true}});
            BX.adjust($sprint_stop, {props: {disabled: false}});

            START_PROCESS = true;

            runProcess({next_action: button.action});
        });
    }

    function start() {
        BX.addClass($sprint_start, 'ui-btn-wait');
        BX.adjust($sprint_start, {props: {disabled: true}});
        BX.adjust($sprint_stop, {props: {disabled: false}});

        BX.cleanNode($sprint_messages);

        BUTTONS.forEach(function ($button) {
            BX.remove($button);
        });
        BUTTONS = [];

        START_PROCESS = true;

        runProcess({next_action: 'start'});
    }

    function stop() {
        BX.removeClass($sprint_start, 'ui-btn-wait');
        BX.adjust($sprint_start, {props: {disabled: false}});
        BX.adjust($sprint_stop, {props: {disabled: true}});

        BUTTONS.forEach(function ($button) {
            BX.removeClass($button, 'ui-btn-wait');
            BX.adjust($button, {props: {disabled: false}});
        });

        NEXT_ACTION = '';
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
        if (fields.buttons && fields.buttons.length > 0) {
            fields.buttons.forEach(item => addButton(item))
            delete fields.buttons;
        }
    }

    function showMessage(message) {
        let css = 'sp-alert-default';
        if (message.color) {
            css = 'sp-alert-' + message.color;
        }

        if (message.id) {
            if (BX(message.id)) {
                BX.adjust(BX(message.id), {
                    props: {className: css},
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
                color: 'danger'
            }))
        }
    }
}
