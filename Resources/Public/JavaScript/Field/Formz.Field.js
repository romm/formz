/**
 * @typedef {(Formz.FieldInstance|Formz.Field.ValidationServiceInstance)} Formz.FullField
 */
Formz.Field = (function () {
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
        var eventsManager = states.eventsManager || Formz.EventsManager.get();

        /**
         * Real field instance returned by the factory.
         *
         * @namespace Formz.FieldInstance
         * @typedef {Formz.FieldInstance} Formz.FieldInstance
         */
        return {
            /**
             * Will empty the feedback container, which can be filled again with
             * the function `insertErrors()`.
             */
            refreshFeedback: function () {
                var errorContainerElement = this.getFeedbackListContainer();

                if (errorContainerElement != null) {
                    errorContainerElement.innerHTML = '';
                }

                eventsManager.dispatch('refreshFeedback');
            },

            /**
             * Will refresh the messages displayed in the feedback container of
             * this field.
             *
             * @param {Object} errors
             */
            insertErrors: function (errors) {
                var errorContainerElement = this.getFeedbackListContainer();

                if (errorContainerElement != null) {
                    if (Formz.objectSize(errors) > 0) {
                        var messageTemplate = this.getMessageTemplate();

                        for (var validationRuleName in errors) {
                            if (errors.hasOwnProperty(validationRuleName)) {
                                for (var errorName in errors[validationRuleName]) {
                                    if (errors[validationRuleName].hasOwnProperty(errorName)) {
                                        errorContainerElement.innerHTML += messageTemplate
                                            .replace('#FIELD#', this.getName())
                                            .replace('#FIELD_ID#', Formz.camelCaseToDashed('formz-' + this.getForm().getName() + '-' + this.getName()))
                                            .replace('#VALIDATOR#', Formz.camelCaseToDashed(validationRuleName))
                                            .replace('#TYPE#', 'error')
                                            .replace('#KEY#', errorName)
                                            .replace('#MESSAGE#', errors[validationRuleName][errorName]);
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
             *   ("formz-field-container"), a query is made to search for an
             *   element containing the attribute "formz-message-template"
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
                    errorTemplateContainer = fieldContainer.querySelector('[formz-message-template="1"]');
                }

                // If the query above returned nothing, we try the same one inside the whole form.
                if (null === errorTemplateContainer) {
                    var flag = {};
                    var allErrorTemplateContainer = this.getForm().getElement().querySelectorAll('[formz-message-template="1"]');
                    var allFieldsErrorTemplateContainer = [];
                    var fields = this.getForm().getFields();

                    for (var field in fields) {
                        if (fields.hasOwnProperty(field)) {
                            var tmpFieldContainer = fields[field].getFieldContainer();
                            if (null !== tmpFieldContainer) {
                                var tmpMessageTemplatesContainer = tmpFieldContainer.querySelectorAll('[formz-message-template="1"]');

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
             * Used to handle the "formz-loading" data attribute of the field
             * container, which can be used with CSS to show loading images next
             * the field (for instance).
             *
             * @param {boolean} run
             */
            handleLoadingBehaviour: function (run) {
                var element = this.getFieldContainer();
                if (null !== element) {
                    if (true === run) {
                        element.setAttribute('formz-loading', '1');
                    } else {
                        element.removeAttribute('formz-loading');
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
            getFeedbackListContainer: function () {
                var selector = configuration['settings']['feedbackContainerSelector'];
                var errorBlockSelector = configuration['settings']['feedbackListSelector'];

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
     * @param {Array}                             states.existingErrors
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

                // Emptying the field feedback container.
                states.field.refreshFeedback();
            });
        })();

        /**
         * Connecting events when the validation result has been handled.
         */
        (function () {
            states.eventsManager.on('validationStops', function () {
                // Turning the loading behaviour off.
                states.field.handleLoadingBehaviour(false);

                // Emptying the field feedback container.
                states.field.refreshFeedback();
                states.field.insertErrors(states.field.getErrors());
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
            var dataAttributesService = Formz.Field.DataAttributesService.get(states.field);

            dataAttributesService.addErrorsDataAttributes(states.existingErrors);
            dataAttributesService.saveAllDataAttributes();

            states.field.onValidationBegins(function () {
                dataAttributesService.refreshValueDataAttribute();
                dataAttributesService.removeValidDataAttribute();
                dataAttributesService.removeErrorsDataAttributes();
            });

            states.field.onValidationDone(function (result) {
                dataAttributesService.removeErrorsDataAttributes();

                if (result.hasErrors()) {
                    dataAttributesService.removeValidDataAttribute();
                    dataAttributesService.addErrorsDataAttributes(states.field.getErrors());
                } else {
                    dataAttributesService.addValidDataAttribute();
                }

                dataAttributesService.saveAllDataAttributes();
            });

            states.eventsManager.on('reactivated', function () {
                dataAttributesService.restoreAllDataAttributes();
            });

            states.eventsManager.on('deactivated', function () {
                dataAttributesService.hideAllDataAttributes();
            });

            states.eventsManager.on('refreshFeedback', function () {
                dataAttributesService.removeErrorsDataAttributes();
            });
        })();

        /**
         * Managing classes which will be added or removed on arbitrary elements
         * depending on the field validation result.
         */
        (function () {
            var classes = Formz.getConfiguration()['view']['classes'];
            var fieldContainer = states.field.getFieldContainer();
            var loopOnClasses = function (type, callback) {
                for (var classKey in classes[type]['items']) {
                    if (classes[type]['items'].hasOwnProperty(classKey)) {
                        var classToFind = 'formz-' + type + '-' + classes[type]['items'][classKey];
                        var classToHandle = classes[type]['items'][classKey];

                        if (Formz.hasClass(fieldContainer, classToFind)) {
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
                        Formz.removeClass(element, className);
                    });
                    loopOnClasses('valid', function (element, className) {
                        Formz.removeClass(element, className);
                    });
                });

                states.field.onValidationDone(function (result) {
                    if (result.hasErrors()) {
                        loopOnClasses('errors', function (element, className) {
                            Formz.addClass(element, className);
                        });
                    } else {
                        loopOnClasses('valid', function (element, className) {
                            Formz.addClass(element, className);
                        });
                    }
                });

                states.eventsManager.on('refreshFeedback', function () {
                    loopOnClasses('errors', function (element, className) {
                        Formz.removeClass(element, className);
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
         * @param {Array}              existingErrors
         * @param {string}             submittedFieldValue
         * @param {boolean}            wasValidated
         * @returns {?Formz.FullField}
         */
        get: function (name, configuration, form, existingErrors, submittedFieldValue, wasValidated) {
            if ('' === name || null === form) {
                return null;
            }

            var states = {
                name: name,
                configuration: configuration,
                existingErrors: existingErrors,
                submittedFieldValue: submittedFieldValue,
                wasValidated: wasValidated,
                form: form,
                eventsManager: Formz.EventsManager.get()
            };

            var fieldProto = states.field = fieldPrototype(states);
            var validationServicePrototype = Formz.Field.ValidationService.getFieldValidationService(states);
            var fieldInstance = Formz.extend(fieldProto, validationServicePrototype);

            manageFieldEvents({
                field: fieldInstance,
                existingErrors: existingErrors,
                eventsManager: states.eventsManager
            });

            return fieldInstance;
        }
    }
})();
