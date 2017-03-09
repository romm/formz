/**
 * Contains the main utilities to create and manage a form.
 *
 * Use the following code to manipulate a registered form.
 *
 * Formz.Form.get('formName', function(form) { ... });
 */
/**
 * @typedef {Formz.FormInstance|Formz.Form.SubmissionServiceInstance} Formz.FullForm
 */
Formz.Form = (function () {
    /**
     * @param {Object}          states
     * @param {HTMLFormElement} states.element
     * @param {Object}          states.configuration
     * @param {String}          states.name
     * @returns {Formz.FormInstance}
     */
    var formPrototype = function (states) {
        /**
         * Contains the DOM element of the form.
         *
         * @type {?HTMLFormElement}
         */
        var element = states.element || null;

        /**
         * Contains the configuration object of the form.
         *
         * @type {Object}
         */
        var configuration = states.configuration || {};

        /**
         * The name of the form. Note that this is the same value as the "name"
         * property of the form DOM element.
         *
         * @type {String}
         */
        var name = states.name || '';

        /**
         * @type {Object}
         */
        var submittedValues = {};

        /**
         * @type {Object<Array>}
         */
        var existingMessages = {};

        /**
         * @type {boolean}
         */
        var formWasValidated = false;

        /**
         * Contains the fields instances of this form.
         *
         * @type {Object<Formz.FullField>}
         */
        var fields = {};

        /**
         * @type {boolean}
         */
        var fieldsHaveBeenInitialized = false;

        /**
         * @type {Array}
         */
        var deactivatedFields = [];

        /**
         * Initializes all the fields of this form: an instance is created for
         * every field.
         *
         * This function will run only once.
         */
        var initializeFields = function () {
            if (fieldsHaveBeenInitialized) {
                return;
            }

            fieldsHaveBeenInitialized = true;

            var configurationFields = configuration['fields'];
            for (var fieldName in configurationFields) {
                if (configurationFields.hasOwnProperty(fieldName)) {
                    var existingErrors = {};
                    var existingWarning = {};
                    var existingNotices = {};

                    if (fieldName in existingMessages) {
                        var fieldExistingMessages = existingMessages[fieldName];

                        if ('errors' in fieldExistingMessages) {
                            existingErrors = fieldExistingMessages['errors'];
                        }
                        if ('warnings' in fieldExistingMessages) {
                            existingWarning = fieldExistingMessages['warnings'];
                        }
                        if ('notices' in fieldExistingMessages) {
                            existingNotices = fieldExistingMessages['notices'];
                        }
                    }

                    var submittedFieldValue = (fieldName in submittedValues)
                        ? submittedValues[fieldName]
                        : '';

                    var isDeactivated = -1 !== deactivatedFields.indexOf(fieldName);

                    var field = Formz.Field.get(
                        fieldName,
                        configurationFields[fieldName],
                        formInstance,
                        existingErrors,
                        existingWarning,
                        existingNotices,
                        submittedFieldValue,
                        formWasValidated,
                        isDeactivated
                    );
                    if (field.getElements().length > 0) {
                        fields[fieldName] = field;
                    }
                }
            }
        };

        /**
         * Real form instance returned by the factory.
         *
         * @namespace Formz.FormInstance
         * @typedef {Formz.FormInstance} Formz.FormInstance
         */
        var formInstance = {
            /**
             * @returns {string}
             */
            getName: function () {
                return name;
            },

            /**
             * @returns {HTMLFormElement}
             */
            getElement: function () {
                var formElements = window.document.getElementsByName(name);

                if (formElements.length === 0) {
                    Formz.debug(
                        'Could not get the DOM element for the form "' + name + '"!',
                        Formz.TYPE_ERROR
                    )
                }

                return formElements[0];
            },

            getConfiguration: function () {
                return configuration;
            },

            /**
             * Returns all the fields of this form.
             *
             * @returns {Object.<Formz.FullField>}
             */
            getFields: function () {
                initializeFields();

                return fields;
            },

            /**
             * Returns the field with the given name, or null if none has this
             * name.
             *
             * @param {string} name
             * @returns {?Formz.FullField}
             */
            getFieldByName: function (name) {
                initializeFields();

                return (name in fields)
                    ? fields[name]
                    : null;
            },

            /**
             * This function takes care of refreshing the validation for the
             * fields which may have been changed with no detection of the whole
             * FormZ system.
             *
             * This actually occurs when the user submits a form, then comes
             * back to it with the return functionality of his browser.
             */
            refreshAllFields: function () {
                for (var fieldName in fields) {
                    if (fields.hasOwnProperty(fieldName)) {
                        var field = fields[fieldName];
                        var fieldValue = field.getValue();
                        fieldValue = (typeof fieldValue === 'object')
                            ? fieldValue.join()
                            : fieldValue;
                        var submittedFieldValue = (fieldName in submittedValues)
                            ? submittedValues[fieldName]
                            : '';
                        if ('' !== fieldValue
                            && submittedFieldValue !== fieldValue
                        ) {
                            field.validate();
                        }
                    }
                }
            },

            /**
             * @param {string}  submittedFormValues
             * @param {string}  existingFormMessages
             * @param {boolean} formValidationDone
             * @param {Array}   deactivatedFieldsNames
             */
            injectRequestData: function (submittedFormValues, existingFormMessages, formValidationDone, deactivatedFieldsNames) {
                submittedValues = submittedFormValues;
                existingMessages = existingFormMessages;
                formWasValidated = formValidationDone;
                deactivatedFields = deactivatedFieldsNames;
            }
        };

        /**
         * When a field is validated, the form is entirely checked to see if all
         * fields are valid, in which case an attribute is added to the form DOM
         * element: `formz-valid`.
         *
         * Several usages can be found for this: for instance, the submission
         * button can be shown only when the form is valid (logical behaviour
         * since the submission is canceled whenever an error is found).
         */
        Formz.Form.get(name, function () {
            var checkFormIsValid = function () {
                var globalFlag = true;
                var fieldsNumber = Formz.objectSize(fields);
                var fieldsChecked = 0;

                /**
                 * Function called every time a field has been checked. When all
                 * fields have been checked, the form attribute is added or
                 * removed, depending on if errors were found.
                 */
                var checkAllFieldsWereProcessed = function () {
                    if (fieldsNumber === fieldsChecked) {
                        if (true === globalFlag) {
                            field.getForm().getElement().setAttribute('formz-valid', '1');
                        } else {
                            field.getForm().getElement().removeAttribute('formz-valid');
                        }
                    }
                };

                /**
                 * Checks if the given field is valid. There can be several
                 * steps, for instance depending on if the field was already
                 * validated.
                 *
                 * @param {Formz.FullField} field
                 */
                var checkFieldIsValid = function (field) {
                    var flag = false;
                    fieldsChecked++;

                    field.getActivatedValidationRules(function (validationRules) {
                        if (0 === Formz.objectSize(validationRules)) {
                            // If the field does not have any validation rule, it is obviously considered valid.
                            flag = true;
                        } else {
                            if (field.wasValidated()) {
                                flag = field.isValid();
                            } else {
                                flag = true;
                                for (var validationName in validationRules) {
                                    if (validationRules.hasOwnProperty(validationName)) {
                                        if (false === validationRules[validationName]['configuration']['acceptsEmptyValues']) {
                                            flag = false;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    globalFlag = globalFlag && flag;
                    checkAllFieldsWereProcessed();
                };

                /*
                 * Looping on each field: if it is activated the full check is
                 * launched to see if it is valid.
                 */
                for (var fieldName in fields) {
                    if (fields.hasOwnProperty(fieldName)) {
                        var field = fields[fieldName];

                        (function (field) {
                            field.checkActivationCondition(
                                function () {
                                    checkFieldIsValid(field);
                                },
                                function () {
                                    fieldsChecked++;
                                    checkAllFieldsWereProcessed();
                                }
                            );
                        })(field);
                    }
                }
            };

            /*
             * Initializing the script: every time a field is validated, the
             * full process to check if the form is valid is launched.
             */
            var fields = formInstance.getFields();
            for (var fieldName in fields) {
                if (fields.hasOwnProperty(fieldName)) {
                    fields[fieldName].onValidationDone(checkFormIsValid);
                }
            }
        });

        return formInstance;
    };

    /**
     * Contains all forms instances.
     *
     * @type {Object}
     */
    var formsRepository = {};

    /**
     * Contains all the callback functions which will run before the form is
     * actually initialized.
     *
     * @type {Object<Array>}
     */
    var beforeInitializationCallbacks = {};

    /**
     * @type {Object<boolean>}
     */
    var beforeInitializationCallbacksDidRun = {};

    /**
     * Will run all the callbacks registered with the function
     * `beforeInitialization()`.
     *
     * @param name
     */
    var runBeforeInitializationCallbacks = function (name) {
        if ('undefined' === typeof beforeInitializationCallbacksDidRun[name]) {
            beforeInitializationCallbacksDidRun[name] = true;

            if ('undefined' !== typeof beforeInitializationCallbacks[name]) {
                for (var i = 0; i < beforeInitializationCallbacks[name].length; i++) {
                    beforeInitializationCallbacks[name][i](formsRepository[name]);
                }
            }
        }
    };

    /** @namespace Formz.Form */
    return {
        /**
         * Registers a new form instance.
         *
         * This is no public API, do not use in your own scripts!
         *
         * @param {string} name           Name of the form.
         * @param {string} configuration
         */
        register: function (name, configuration) {
            var form = formPrototype({
                name: name,
                configuration: configuration
            });

            formsRepository[name] = Formz.extend(
                form,
                Formz.Form.SubmissionService.get(name)
            );
        },

        /**
         * Declaring the callback used by the function `get()`.
         *
         * @callback Formz.getFormCallBack
         * @param {Formz.FullForm} form
         */

        /**
         * Use this function to run your own processes on a given form.
         *
         * A callback is used to prevent any action before the DOM is loaded and
         * the form element exists.
         *
         * @param {string}                name     Name of the form.
         * @param {Formz.getFormCallBack} callback Function called if the form is found.
         */
        get: function (name, callback) {
            if (typeof callback === 'function') {
                var func = function () {
                    if (name in formsRepository) {
                        runBeforeInitializationCallbacks(name);
                        callback(formsRepository[name]);
                    }
                };

                if (document.readyState != 'loading') {
                    func();
                } else {
                    document.addEventListener('DOMContentLoaded', func);
                }
            }
        },

        /**
         * Use this function to register a callback which must run before the
         * form is actually initialized.
         *
         * @param {string}   name
         * @param {callback} callback
         */
        beforeInitialization: function (name, callback) {
            if (typeof callback === 'function') {
                if ('undefined' === typeof beforeInitializationCallbacks[name]) {
                    beforeInitializationCallbacks[name] = [];
                }

                beforeInitializationCallbacks[name].push(callback);
            }
        }
    };
})();
