(function() {
    // @todo
    var checkValues = {};

    /**
     * @param {Object}               messages
     * @param {string}               type
     * @param {Formz.ResultInstance} result
     */
    var addMessages = function(messages, type, result) {
        if (type in messages) {
            for (var key in messages[type]) {
                if (messages[type].hasOwnProperty(key)) {
                    var message = {
                        name: key,
                        message: messages[type][key]
                    };

                    if (type === 'errors') {
                        result.addError(message);
                    } else if (type === 'warnings') {
                        result.addWarning(message);
                    } else if (type === 'notices') {
                        result.addNotice(message);
                    }
                }
            }
        }
    };

    Formz.Validation.registerValidator(
        'AjaxValidator',
        /**
         * @param {string}                   value
         * @param {Function}                 callback
         * @param {Object}                   states
         * @param {Formz.ResultInstance}     states.result
         * @param {Object}                   states.data
         * @param {string}                   states.validatorName
         * @param {object}                   states.configuration
         */
        function (value, callback, states) {
            /** @type {Formz.FullField} field */
            var field = states.data.field;

            if (value !== '') {
                var addDefaultError = function() {
                    var message = field.getForm().getConfiguration()['settings']['defaultErrorMessage'];

                    states['result'].addError({
                        name: 'default',
                        message: message
                    });
                };
                var errorCallBack = function() {
                    addDefaultError();
                    callback();
                };

                var request = new XMLHttpRequest();
                request.open('POST', Formz.getAjaxUrl(), true);
                request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                request.onerror = errorCallBack;
                request.onload = function() {
                    if (request.status < 200 || request.status >= 400) {
                        errorCallBack();
                    } else {
                        try {
                            var ajaxResult = JSON.parse(request.responseText);

                            if ('messages' in ajaxResult) {
                                var messages = ajaxResult['messages'];

                                addMessages(messages, 'errors', states['result']);
                                addMessages(messages, 'warnings', states['result']);
                                addMessages(messages, 'notices', states['result']);
                            }

                            if (false === ajaxResult['success']
                                && false === states['result'].hasErrors()
                            ) {
                                addDefaultError();
                            }

                            callback();
                        } catch(e) {
                            errorCallBack();
                        }
                    }
                };

                var data = 'formClassName=' + encodeURIComponent(field.getForm().getConfiguration()['className']);
                data += '&formName=' + encodeURIComponent(field.getForm().getName());
                data += '&validatorName=' + encodeURIComponent(states['validatorName']);
                data += '&fieldName=' + encodeURIComponent(field.getName());
                data += '&' + Formz.buildQueryForm(field.getForm().getElement(), 'form');

                request.send(data);
            } else {
                callback();
            }
        }
    );
})();
