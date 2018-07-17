/**
 * @typedef {(Formz.FieldInstance|Formz.Field.ValidationServiceInstance)} Formz.FullField
 */
Fz.Field = (function () {
    /**
     * This callbacks are called when a validation is running.
     *
     * @callback validationResultCallback
     * @param {Formz.ResultInstance} result The result of the validation.
     */

    /**
     * @callback validationCallback
     * @param {Formz.FieldInstance} field
     * @param {Function}            continueValidation
     */

    /**
     * @param {Object}                      states
     * @param {String}                      states.name
     * @param {Formz.FormInstance}          states.form
     * @param {Object}                      states.configuration
     * @param {Formz.EventsManagerInstance} states.eventsManager
     * @returns {Formz.FieldInstance}
     */
    var fieldPrototype = function (states) {
        /**
         * The name of the field.
         *
         * @type {string}
         */
        var name = states.name || '';

        /**
         * Contains the form instance which includes this field.
         *
         * @type {Formz.FormInstance}
         */
        var form = states.form || null;

        /**
         * Contains the configuration object of the field.
         *
         * @type {Object}
         */
        var configuration = states.configuration || {};

        /**
         * Contains all the DOM elements of this field.
         *
         * @type {NodeList}
         */
        var elements;

        /**
         * @type {Formz.EventsManagerInstance}
         */
        var eventsManager = states.eventsManager || Fz.EventsManager.get();

        /**
         * Real field instance returned by the factory.
         *
         * @namespace Formz.FieldInstance
         * @typedef {Formz.FieldInstance} Formz.FieldInstance
         */
        return {
            /**
             * Will empty the message container, which can be filled again with
             * the function `insertMessages()`.
             */
            refreshMessage: function () {
                var messageListContainerElement = this.getMessageListContainer();

                if (messageListContainerElement != null) {
                    messageListContainerElement.innerHTML = '';
                }

                eventsManager.dispatch('refreshMessages');
            },

            /**
             * Will refresh the messages displayed in the message container of
             * this field.
             *
             * @param {Object} messages
             * @param {string} type
             */
            insertMessages: function (messages, type) {
                var messageListContainerElement = this.getMessageListContainer();

                if (messageListContainerElement != null) {
                    if (Fz.objectSize(messages) > 0) {
                        var messageTemplate = this.getMessageTemplate();

                        for (var validationRuleName in messages) {
                            if (messages.hasOwnProperty(validationRuleName)) {
                                for (var name in messages[validationRuleName]) {
                                    if (messages[validationRuleName].hasOwnProperty(name)) {
                                        messageListContainerElement.innerHTML += messageTemplate
                                            .split('#FIELD#').join(this.getName())
                                            .split('#FIELD_ID#').join(Fz.camelCaseToDashed('fz-' + this.getForm().getName() + '-' + this.getName()))
                                            .split('#VALIDATOR#').join(Fz.camelCaseToDashed(validationRuleName))
                                            .split('#TYPE#').join(type)
                                            .split('#KEY#').join(name)
                                            .split('#MESSAGE#').join(messages[validationRuleName][name]);
                                    }
                                }
                            }
                        }
                    }
                }
            },

            /**
             * There are several ways to declare a message template. First, it
             * is fetched from the field settings (the property
             * "messageTemplate"): it is the default value. Then, two
             * possibilities:
             * - If a container for the field is declared
             *   ("fz-field-container"), a query is made to search for an
             *   element containing the attribute "fz-message-template"
             *   inside the field container.
             * - If no container is declared, the same query is made, but inside
             *   the whole form element.
             *
             * If one of these queries above returns an element, then the inner
             * HTML value of this element is used as the message template
             * instead of the field default setting.
             *
             * @returns {string}
             */
            getMessageTemplate: function () {
                var errorTemplate = configuration['settings']['messageTemplate'];
                var errorTemplateContainer = null;
                var fieldContainer = this.getFieldContainer();

                // First: try to fetch for an element inside the field container.
                if (null !== fieldContainer) {
                    errorTemplateContainer = fieldContainer.querySelector('[fz-message-template="1"]');
                }

                // If the query above returned nothing, we try the same one inside the whole form.
                if (null === errorTemplateContainer) {
                    var flag = {};
                    var allErrorTemplateContainer = this.getForm().getElement().querySelectorAll('[fz-message-template="1"]');
                    var allFieldsErrorTemplateContainer = [];
                    var fields = this.getForm().getFields();

                    for (var field in fields) {
                        if (fields.hasOwnProperty(field)) {
                            var tmpFieldContainer = fields[field].getFieldContainer();
                            if (null !== tmpFieldContainer) {
                                var tmpMessageTemplatesContainer = tmpFieldContainer.querySelectorAll('[fz-message-template="1"]');

                                for (var k = 0; k < tmpMessageTemplatesContainer.length; k++) {
                                    allFieldsErrorTemplateContainer.push(tmpMessageTemplatesContainer[k]);
                                }
                            }
                        }
                    }

                    for (var i = 0; i < allErrorTemplateContainer.length; i++) {
                        flag[i] = false;
                        for (var j = 0; j < allFieldsErrorTemplateContainer.length; j++) {
                            if (allErrorTemplateContainer[i] === allFieldsErrorTemplateContainer[j]) {
                                flag[i] = true;
                            }
                        }
                    }

                    for (i = 0; i < allErrorTemplateContainer.length; i++) {
                        if (false === flag[i]) {
                            errorTemplateContainer = allErrorTemplateContainer[i];
                        }
                    }
                }

                if (null !== errorTemplateContainer) {
                    errorTemplate = errorTemplateContainer.innerHTML;
                }

                return errorTemplate;
            },

            /**
             * Used to handle the "fz-loading" data attribute of the field
             * container, which can be used with CSS to show loading images next
             * the field (for instance).
             *
             * @param {boolean} run
             */
            handleLoadingBehaviour: function (run) {
                var element = this.getFieldContainer();
                if (null !== element) {
                    var formElement = this.getForm().getElement();
                    if (true === run) {
                        element.setAttribute('fz-loading', '1');
                        formElement.setAttribute('fz-loading', '1');
                    } else {
                        element.removeAttribute('fz-loading');
                        formElement.removeAttribute('fz-loading');
                    }
                }
            },

            /**
             * @returns {string}
             */
            getName: function () {
                return name;
            },

            /**
             * Returns the current value of the field.
             *
             * Depending on the type of the field, the result can be either a
             * string containing the plain value of the field input, or an array
             * of selected values (for checkbox and radio inputs for instance).
             *
             * @returns {?string|Array}
             */
            getValue: function () {
                var result = '';
                var elements = this.getElements();

                if (elements.length > 0) {
                    // If the field does have only one element, we return its value.
                    if (1 === elements.length) {
                        if (elements[0].type == 'checkbox'
                            || elements[0].type == 'radio'
                        ) {
                            if (true === elements[0].checked) {
                                result = elements[0].value;
                            }
                        } else {
                            result = elements[0].value;
                        }
                    } else {
                        // The field does have several elements, we return the value of each selected element.
                        result = [];
                        for (var i = 0; i < elements.length; i++) {
                            if ((elements[i].type == 'checkbox'
                                    || elements[i].type == 'radio'
                                )
                                && true === elements[i].checked
                            ) {
                                result.push(elements[i].value);
                            }
                        }
                    }
                }

                return result;
            },

            /**
             * @returns {Object}
             */
            getConfiguration: function () {
                return configuration;
            },

            /**
             * @returns {Formz.FormInstance}
             */
            getForm: function () {
                return form;
            },

            /**
             * @returns {NodeList}
             */
            getElements: function () {
                // We first try to see if this field has several elements, for instance checkbox.
                elements = form.getElement().querySelectorAll('[name$="[' + form.getName() + '][' + name + '][]"]');
                if (elements.length === 0) {
                    // If none was found, we try to select a single element.
                    elements = form.getElement().querySelectorAll('[name$="[' + form.getName() + '][' + name + ']"]');
                }

                return elements;
            },

            /**
             * Returns the first DOM element of the field.
             *
             * Please note that the field can also have several elements, for
             * instance if it is multiple checkbox/radio. In this case, prefer
             * using the function `getElements()`.
             *
             * @returns {?Element}
             */
            getElement: function () {
                elements = this.getElements();

                return (elements.length > 0)
                    ? elements[0]
                    : null;
            },

            /**
             * @returns {?Element}
             */
            getFieldContainer: function () {
                var selector = configuration['settings']['fieldContainerSelector'];

                return ('' !== selector)
                    ? this.getForm().getElement().querySelector(selector)
                    : null;
            },

            /**
             * @returns {?Element}
             */
            getMessageListContainer: function () {
                var selector = configuration['settings']['messageContainerSelector'];
                var errorBlockSelector = configuration['settings']['messageListSelector'];

                if (null !== errorBlockSelector && '' !== errorBlockSelector) {
                    selector += ' ' + errorBlockSelector;
                }

                return ('' !== selector)
                    ? this.getForm().getElement().querySelector(selector)
                    : null;
            }
        };
    };

    /**
     * @param {Object}                            states
     * @param {Formz.FullField}                   states.field
     * @param {Object}                            states.existingErrors
     * @param {Object}                            states.existingWarnings
     * @param {Object}                            states.existingNotices
     * @param {Formz.EventsManagerInstance}       states.eventsManager
     */
    var manageFieldEvents = function (states) {
        /**
         * Creating DOM events firing this field validation.
         *
         * E.g. if the field is a `<select>`, a validation event will be
         * triggered when the value is changed.
         */
        (function () {
            var trimField = function () {
                this.value = this.value.replace(/^\s+|\s+$/g, '');
            };
            
            var validateCallback = function () {
                states.field.validate();
            };

            var elements = states.field.getElements();
            for (var elementName in elements) {
                if (elements.hasOwnProperty(elementName)) {
                    var element = elements[elementName];
                    if (typeof element.type !== 'undefined') {
                        if (element.type === 'radio') {
                            element.onclick = validateCallback;
                        } else if (element.type === 'checkbox') {
                            element.addEventListener('change', validateCallback);
                        } else if (element.type.substr(0, 6) === 'select') {
                            element.addEventListener('change', validateCallback);
                        } else if (element.type === 'text') {
                            element.addEventListener('blur', trimField);
                            element.addEventListener('blur', validateCallback);
                        } else {
                            element.addEventListener('blur', validateCallback);
                        }
                    }
                }
            }
        })();

        /**
         * Connecting events when the validation of the field begins.
         *
         * The events are directly bound to the validation service.
         */
        (function () {
            states.field.onValidationBegins(function () {
                // Turning the loading behaviour on.
                states.field.handleLoadingBehaviour(true);

                // Emptying the field message container.
                states.field.refreshMessage();
            });
        })();

        /**
         * Connecting events when the validation result has been handled.
         */
        (function () {
            states.eventsManager.on('validationStops', function () {
                // Turning the loading behaviour off.
                states.field.handleLoadingBehaviour(false);

                // Emptying the field message container.
                states.field.refreshMessage();
                states.field.insertMessages(states.field.getErrors(), 'error');
                states.field.insertMessages(states.field.getWarnings(), 'warning');
                states.field.insertMessages(states.field.getNotices(), 'notice');
            });

            states.field.onValidationDone(function () {
                // Refreshing other fields validation.
                var fields = states.field.getForm().getFields();

                for (var fieldName in fields) {
                    if (fields.hasOwnProperty(fieldName)
                        && fieldName !== states.field.getName()
                    ) {
                        fields[fieldName].refreshValidation();
                    }
                }
            });
        })();

        /**
         * Managing data-attributes for the field.
         */
        (function () {
            var dataAttributesService = Fz.Field.DataAttributesService.get(states.field);

            dataAttributesService.addMessagesDataAttributes(states.existingErrors, 'error');
            dataAttributesService.addMessagesDataAttributes(states.existingWarnings, 'warning');
            dataAttributesService.addMessagesDataAttributes(states.existingNotices, 'notice');
            dataAttributesService.saveAllDataAttributes();

            states.field.onValidationBegins(function () {
                dataAttributesService.refreshValueDataAttribute();
                dataAttributesService.removeValidDataAttribute();
                dataAttributesService.removeMessagesDataAttributes();
            });

            states.field.onValidationDone(function (result) {
                dataAttributesService.removeMessagesDataAttributes();

                if (result.hasErrors()) {
                    dataAttributesService.removeValidDataAttribute();
                } else {
                    dataAttributesService.addValidDataAttribute();
                }

                dataAttributesService.addMessagesDataAttributes(states.field.getErrors(), 'error');
                dataAttributesService.addMessagesDataAttributes(states.field.getWarnings(), 'warning');
                dataAttributesService.addMessagesDataAttributes(states.field.getNotices(), 'notice');

                dataAttributesService.saveAllDataAttributes();
            });

            states.eventsManager.on('reactivated', function () {
                dataAttributesService.restoreAllDataAttributes();
            });

            states.eventsManager.on('deactivated', function () {
                dataAttributesService.hideAllDataAttributes();
            });

            states.eventsManager.on('refreshMessages', function () {
                dataAttributesService.removeMessagesDataAttributes();
            });
        })();

        /**
         * Managing classes which will be added or removed on arbitrary elements
         * depending on the field validation result.
         */
        (function () {
            var classes = Fz.getConfiguration()['view']['classes'];
            var fieldContainer = states.field.getFieldContainer();
            var loopOnClasses = function (type, callback) {
                for (var classKey in classes[type]['items']) {
                    if (classes[type]['items'].hasOwnProperty(classKey)) {
                        var classToFind = 'fz-' + type + '-' + classes[type]['items'][classKey];
                        var classToHandle = classes[type]['items'][classKey];

                        if (Fz.hasClass(fieldContainer, classToFind)) {
                            callback(fieldContainer, classToHandle);
                        }

                        var elements = fieldContainer.querySelectorAll('.' + classToFind);
                        for (var i = 0; i < elements.length; i++) {
                            callback(elements[i], classToHandle);
                        }
                    }
                }
            };

            if (null !== fieldContainer) {
                states.field.onValidationBegins(function () {
                    loopOnClasses('errors', function (element, className) {
                        Fz.removeClass(element, className);
                    });
                    loopOnClasses('valid', function (element, className) {
                        Fz.removeClass(element, className);
                    });
                });

                states.field.onValidationDone(function (result) {
                    if (result.hasErrors()) {
                        loopOnClasses('errors', function (element, className) {
                            Fz.addClass(element, className);
                        });
                    } else {
                        var value = states.field.getValue();

                        if (typeof value === 'string'
                            && value !== ''
                            || typeof value === 'object'
                            && value.length > 0
                        ) {
                            loopOnClasses('valid', function (element, className) {
                                Fz.addClass(element, className);
                            });
                        }
                    }
                });

                states.eventsManager.on('refreshMessages', function () {
                    loopOnClasses('errors', function (element, className) {
                        Fz.removeClass(element, className);
                    });
                });
            }
        })();
    };

    /** @namespace Formz.Field */
    return {
        /**
         * Returns a full instance of a field: a field prototype with a full
         * running validation service.
         *
         * @param {string}             name
         * @param {Object}             configuration
         * @param {Formz.FormInstance} form
         * @param {Object}             existingErrors
         * @param {Object}             existingWarnings
         * @param {Object}             existingNotices
         * @param {string}             submittedFieldValue
         * @param {boolean}            wasValidated
         * @param {boolean}            isDeactivated
         * @returns {?Formz.FullField}
         */
        get: function (name, configuration, form, existingErrors, existingWarnings, existingNotices, submittedFieldValue, wasValidated, isDeactivated) {
            if ('' === name || null === form) {
                return null;
            }

            var states = {
                name: name,
                configuration: configuration,
                existingErrors: existingErrors,
                existingWarnings: existingWarnings,
                existingNotices: existingNotices,
                submittedFieldValue: submittedFieldValue,
                wasValidated: wasValidated,
                isDeactivated: isDeactivated,
                form: form,
                eventsManager: Fz.EventsManager.get()
            };

            var fieldProto = states.field = fieldPrototype(states);
            var validationServicePrototype = Fz.Field.ValidationService.getFieldValidationService(states);
            var fieldInstance = Fz.extend(fieldProto, validationServicePrototype);

            manageFieldEvents({
                field: fieldInstance,
                existingErrors: existingErrors,
                existingWarnings: existingWarnings,
                existingNotices: existingNotices,
                eventsManager: states.eventsManager
            });

            return fieldInstance;
        }
    }
})();
