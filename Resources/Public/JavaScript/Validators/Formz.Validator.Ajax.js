(function() {
    var controllerNamespace = 'tx_formz_ajaxvalidation';

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

    Fz.Validation.registerValidator(
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
                request.open('POST', Fz.getAjaxUrl(), true);
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

                var data = wrapArgument('className', field.getForm().getConfiguration()['className'])
                    + '&' + wrapArgument('name', field.getForm().getName())
                    + '&' + wrapArgument('fieldName', field.getName())
                    + '&' + wrapArgument('validatorName', states['validatorName'])
                    + '&' + buildQueryForm(field.getForm().getElement());

                request.send(data);
            } else {
                callback();
            }
        }
    );

    /**
     * @param {string} name
     * @param {string} value
     * @returns {string}
     */
    var wrapArgument = function(name, value) {
        return controllerNamespace + '[' + name + ']=' + encodeURIComponent(value)
    };

    var buildQueryForm = function (form) {
        var query = '';
        var elementsDone = {};
        for (var i = 0; i < form.elements.length; i++) {
            var key = form.elements[i].name;
            if ('' !== key
                && 'undefined' !== typeof key
                && false == key in elementsDone
                && null === key.match(/\[__referrer\]/)
            ) {
                var value = getElementValue(form.elements[i]);
                if (value) {
                    if ('' !== query) {
                        query += '&';
                    }

                    key = key.replace(/\w+/, function () {
                        return controllerNamespace;
                    });

                    query += encodeURIComponent(key) + '=' + encodeURIComponent(value);

                    elementsDone[key] = true;
                }
            }
        }

        return query;
    };

    var getElementValue = function (formElement) {
        var type = null;
        if (formElement.length != null) {
            type = formElement[0].type;
        }
        if (typeof(type) == 'undefined' || type == 0 || null == type) {
            type = formElement.type;
        }

        var x = 0;

        switch (type) {
            case 'undefined':
                return;

            case 'radio':
                var checkedOne = document.querySelector('[name="' + formElement.name + '"]:checked');
                if (null !== checkedOne) {
                    return checkedOne.value;
                }

                return;

            case 'select-multiple':
                var myArray = [];
                for (x = 0; x < formElement.length; x++) {
                    if (formElement[x].selected == true) {
                        myArray[myArray.length] = formElement[x].value;
                    }
                }

                return myArray;

            case 'checkbox':
                if (formElement.checked) {
                    return formElement.value;
                } else {
                    return formElement.checked;
                }

            default:
                return formElement.value;
        }
    };
})();
