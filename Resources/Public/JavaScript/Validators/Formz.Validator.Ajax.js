(function() {
    // @todo
    var checkValues = {};

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

                            if (false === ajaxResult['success']) {
                                if ('message' in ajaxResult) {
                                    states['result'].addError({
                                        name: 'default',
                                        message: ajaxResult['message']
                                    });
                                } else {
                                    addDefaultError();
                                }
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
                data += '&' + Formz.buildQueryForm(field.getForm().getElement(), 'fieldValue');

                request.send(data);
            } else {
                callback();
            }
        }
    );
})();
