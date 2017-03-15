Fz.Field.ValidationService = (function () {
    /**
     * @param {Object}                             states
     * @param {Function}                           states.valueCallback
     * @param {Function}                           states.validationDataCallback
     * @param {Formz.Field.EventsManagerInstance}  states.eventsManager
     * @param {String}                             states.defaultErrorMessage
     * @param {Object}                             states.existingErrors
     * @param {Object}                             states.existingWarnings
     * @param {Object}                             states.existingNotices
     * @param {string}                             states.submittedFieldValue
     * @param {boolean}                            states.wasValidated
     * @param {boolean}                            states.isDeactivated
     * @returns {Formz.Field.ValidationServiceInstance}
     */
    var servicePrototype = function (states) {
        /**
         * This is the callback function which will be used to get the current
         * value which should be validated.
         *
         * @type {Function}
         */
        var valueCallback = states.valueCallback || function () {
                return '';
            };

        /**
         * Returns a string version of the value returned by `valueCallback()`:
         * if the result is an array, it is converted to a csv.
         *
         * @returns {String}
         */
        var getStringValue = function () {
            var value = valueCallback();
            if (typeof value === 'object') {
                value = value.join();
            }

            return value;
        };

        /**
         * This function will be called to send any third-party data to every
         * validator, during the validation process.
         *
         * @type {Function}
         */
        var validationDataCallback = states.validationDataCallback || function () {
                return {};
            };

        /**
         * @type {Formz.EventsManagerInstance}
         */
        var eventsManager = states.eventsManager || Fz.EventsManager.get();

        /**
         * Default error message used if no valid message is found for an error.
         *
         * @type {String}
         */
        var defaultErrorMessage = states.defaultErrorMessage || 'Error';

        /**
         * Contains all the validation rules added to this service.
         *
         * @type {Object<String, Object>}
         */
        var validationRules = {};

        /**
         * Flag which is set to true when a validation is currently running with
         * this service. It prevents infinite loop, as some deep functions call
         * can try to run this service validation again. When all process is
         * done, the flag is reset to false.
         *
         * @type {boolean}
         */
        var validationRunning = false;

        /**
         * Contains the current errors found by this validation service.
         *
         * @type {Object<Object>}
         */
        var errors = states.existingErrors || {};

        /**
         * Contains the current warnings found by this validation service.
         *
         * @type {Object<Object>}
         */
        var warnings = states.existingWarnings || {};

        /**
         * Contains the current notices found by this validation service.
         *
         * @type {Object<Object>}
         */
        var notices = states.existingNotices || {};

        /**
         * @type {boolean}
         */
        var wasValidated = states.wasValidated || false;

        /**
         * Contains the last value which the field has when its last validation
         * process did run.
         *
         * @type {string}
         */
        var lastValidatedValue = states.submittedFieldValue || '';

        /**
         * Contains the name of the last validation rule which returned an error
         * during the last validation process of this service.
         *
         * @type {?string}
         */
        var lastValidationErrorName = null;

        /**
         * Contains the name of all validators which were checked during the
         * last validation process.
         *
         * @type {Array|string}
         */
        var validatorsCheckedDuringLastValidation = (true === states.wasValidated)
            ? '*'
            : [];

        /**
         * @type {boolean}
         */
        var isDeactivated = states.isDeactivated;

        /**
         * Internal recursive function to validate the field.
         *
         * @param {Object}        validationRules     The validation rules which are checked.
         * @param {Array}         validationRulesKeys Keys of the remaining validation rules to check (used for recursive behaviour).
         */
        var launchValidation = function (validationRules, validationRulesKeys) {
            var validatorName = validationRulesKeys[0];
            var validationRule = validationRules[validatorName];
            var result = Fz.Result.get({defaultErrorMessage: defaultErrorMessage});
            var finalValidationCallback = function () {
                handleValidationResult(validatorName, result);
            };

            var continueCallback = function () {
                /** @type {validationResultCallback} */
                var callback;

                callback = function (result) {
                    if (false === result.hasErrors()
                        && validationRulesKeys.length > 1
                    ) {
                        // Handling the remaining rules.
                        validationRulesKeys.splice(0, 1);
                        launchValidation(validationRules, validationRulesKeys);
                    } else {
                        // The result has errors, or no more rule to check: the checks stop and the result is handled.
                        finalValidationCallback();
                    }
                };

                if (typeof validationRule === 'undefined') {
                    finalValidationCallback();
                } else {
                    validatorsCheckedDuringLastValidation.push(validatorName);

                    validationRule['validator'].validate(
                        valueCallback(),
                        result,
                        validationDataCallback(),
                        validatorName,
                        validationRule['configuration'],
                        callback
                    );
                }
            };

            var stopCallback = function () {
                if (validationRulesKeys.length > 1) {
                    // Handling the remaining rules.
                    validationRulesKeys.splice(0, 1);
                    launchValidation(validationRules, validationRulesKeys);
                } else {
                    // No more rule to check: the checks stop.
                    finalValidationCallback();
                }
            };

            serviceInstance.checkActivationConditionForValidator(validatorName, continueCallback, stopCallback);
        };

        /**
         * Internal function which will check all the given conditions, if they
         * all return true, then "runCallback" is called. If one of the
         * conditions returns false, "stopCallback" is called.
         *
         * @param {Object<function>} conditions
         * @param {Function}         runCallback
         * @param {Function}         stopCallback
         */
        var checkConditionInternal = function (conditions, runCallback, stopCallback) {
            var result = true;
            var conditionNumber = 0;
            var conditionDone = 0;
            var completeCallback = function (success) {
                success = (typeof success !== 'undefined')
                    ? (success.toString() === 'true')
                    : false;

                result = result && success;

                conditionDone++;
                if (conditionNumber === conditionDone) {
                    if (result) {
                        runCallback();
                    } else if (typeof stopCallback === 'function') {
                        stopCallback();
                    }
                }
            };

            for (var name in conditions) {
                if (conditions.hasOwnProperty(name)) {
                    conditionNumber++;
                    var conditionCallback = conditions[name];
                    if (typeof conditionCallback === 'function') {
                        conditionCallback(this, completeCallback);
                    }
                }
            }

            if (conditionNumber === 0) {
                runCallback();
            }
        };

        /**
         * This function is called when the validation process has ended. It
         * will handle both the cases when the field has errors and when it
         * is valid.
         *
         * It will also handle and refresh the several data attributes of
         * the form element:
         *  - fz-valid-{name} : is added to the form element if the
         *     current field had no errors during its previous validation.
         *     Example : fz-valid-email="1"
         *  - fz-error-{name}-{validationName}-{errorMessage} : is used
         *     for each error of the current field.
         *     Example : fz-error-email-required-default="1"
         *
         * @param {string}               validationRuleName Last validation rule name.
         * @param {Formz.ResultInstance} result             Result of the last validation.
         */
        var handleValidationResult = function (validationRuleName, result) {
            validationRunning = false;
            if (result.hasErrors()) {
                var errors = result.getErrors();

                for (var errorName in errors) {
                    if (errors.hasOwnProperty(errorName)) {
                        addError(validationRuleName, errorName, errors[errorName]);
                    }
                }
            }

            if (result.hasWarnings()) {
                var warnings = result.getWarnings();

                for (var warningName in warnings) {
                    if (warnings.hasOwnProperty(warningName)) {
                        addWarning(validationRuleName, warningName, warnings[warningName]);
                    }
                }
            }

            if (result.hasNotices()) {
                var notices = result.getNotices();

                for (var noticeName in notices) {
                    if (notices.hasOwnProperty(noticeName)) {
                        addNotice(validationRuleName, noticeName, notices[noticeName]);
                    }
                }
            }

            eventsManager.dispatch('validationStops');
            eventsManager.dispatch('validationDone', result);

            if (result.hasErrors()) {
                handleErrorsCallBack();
            }
        };

        /**
         * Will dispatch the events bound to specific errors.
         */
        var handleErrorsCallBack = function () {
            var errors = serviceInstance.getErrors();
            for (var validationName in errors) {
                if (errors.hasOwnProperty(validationName)) {
                    for (var errorName in errors[validationName]) {
                        if (errors[validationName].hasOwnProperty(errorName)) {
                            eventsManager.dispatch('validationError.' + Fz.camelCaseToDashed(validationName));
                            eventsManager.dispatch('validationError.' + Fz.camelCaseToDashed(validationName) + '.' + Fz.camelCaseToDashed(errorName));
                        }
                    }
                }
            }
        };

        /**
         * Adds an error to this field.
         *
         * @param {string} validatorName Name of the validator which returned the error.
         * @param {string} name          Name of the error, e.g. "default".
         * @param {string} message       Message of the error.
         */
        var addError = function (validatorName, name, message) {
            if (false == validatorName in errors) {
                errors[validatorName] = {};
            }
            errors[validatorName][name] = message;
            lastValidationErrorName = validatorName;
        };

        /**
         * Adds an warning to this field.
         *
         * @param {string} validatorName Name of the validator which returned the warning.
         * @param {string} name          Name of the warning, e.g. "default".
         * @param {string} message       Message of the warning.
         */
        var addWarning = function (validatorName, name, message) {
            if (false == validatorName in warnings) {
                warnings[validatorName] = {};
            }
            warnings[validatorName][name] = message;
        };

        /**
         * Adds an notice to this field.
         *
         * @param {string} validatorName Name of the validator which returned the notice.
         * @param {string} name          Name of the notice, e.g. "default".
         * @param {string} message       Message of the notice.
         */
        var addNotice = function (validatorName, name, message) {
            if (false == validatorName in notices) {
                notices[validatorName] = {};
            }
            notices[validatorName][name] = message;
        };

        /**
         * Resets the errors of this field.
         */
        var resetMessages = function () {
            lastValidationErrorName = null;
            errors = {};
            warnings = {};
            notices = {};
        };

        var activate = function () {
            if (true === isDeactivated) {
                isDeactivated = false;
                eventsManager.dispatch('reactivated');
            }
        };

        var deactivate = function () {
            if (false === isDeactivated) {
                isDeactivated = true;
                eventsManager.dispatch('deactivated');
            }
        };

        /**
         * @namespace Formz.Field.ValidationServiceInstance
         * @typedef {Formz.Field.ValidationServiceInstance} Formz.Field.ValidationServiceInstance
         */
        var serviceInstance = {
            /**
             * Returns true if the field has been validated, and no errors were
             * found.
             *
             * Please be aware that the usage of this function is useful only
             * when the validation did actually run, so you should always use
             * this function after having checked the result of the function
             * `wasValidated()`, or in a callback of `onValidationDone()`.
             *
             * @returns {boolean}
             */
            isValid: function () {
                return (
                    0 === Fz.objectSize(errors)
                    && false === validationRunning
                    && true === this.wasValidated()
                );
            },

            /**
             * Will check if the current value has already been validated.
             *
             * Please note that this function should always be called _before_
             * using the function `isValid()` or `hasError()`, for instance.
             *
             * @returns {boolean}
             */
            wasValidated: function () {
                return (wasValidated && getStringValue() === lastValidatedValue);
            },

            /**
             * Will check if the last validation returned a specific error.
             *
             * If the parameter `errorName` is null, if any error is found for
             * the validation `validationName`, true is returned.
             *
             * Please be aware that the usage of this function is useful only
             * when the validation did actually run, so you should always use
             * this function after having checked the result of the function
             * `wasValidated()`.
             *
             * @param {String} validationName
             * @param {?String} errorName
             * @returns {boolean}
             */
            hasError: function (validationName, errorName) {
                var flag = false;

                if (validationName in errors) {
                    if (null === errorName
                        || errorName in errors[validationName]
                    ) {
                        flag = true;
                    }
                }

                return flag;
            },

            /**
             * Starts the validation process for this service. All validation
             * condition will be checked, and all validation rules will run.
             */
            validate: function () {
                if (true === validationRunning) {
                    return;
                }

                validationRunning = true;

                resetMessages();
                validatorsCheckedDuringLastValidation = [];
                wasValidated = true;
                lastValidatedValue = getStringValue();
                eventsManager.dispatch('validationBegins');

                var continueCallback = function () {
                    var validationRulesKeys = Object.keys(validationRules);
                    launchValidation(validationRules, validationRulesKeys);
                };

                var stopValidationCallBack = function () {
                    eventsManager.dispatch('validationStops');
                    validationRunning = false;
                };

                this.checkActivationCondition(continueCallback, stopValidationCallBack);
            },

            /**
             * This function will build a list of all the activated validation
             * rules at this moment. When the list is built, `callback` is
             * called, with as given argument the list of activated validation
             * rules names.
             *
             * @param {Function} callback
             */
            getActivatedValidationRules: function (callback) {
                var activatedValidations = {};
                var maxValidationRules = Fz.objectSize(validationRules);
                var checkedValidationRules = 0;

                var incrementCheckedValidationRules = function () {
                    checkedValidationRules++;
                    if (checkedValidationRules === maxValidationRules) {
                        callback(activatedValidations);
                    }
                };

                if (0 === maxValidationRules) {
                    callback(activatedValidations);
                } else {
                    for (var validatorName in validationRules) {
                        if (validationRules.hasOwnProperty(validatorName)) {
                            (function (validatorName) {
                                serviceInstance.checkActivationConditionForValidator(
                                    validatorName,
                                    function () {
                                        activatedValidations[validatorName] = validationRules[validatorName];
                                        incrementCheckedValidationRules();
                                    },
                                    incrementCheckedValidationRules
                                );
                            }(validatorName))
                        }
                    }
                }
            },

            /**
             * Binds a new validation rule to this instance if the service. It
             * will then be used during the validation process to tell if the
             * current value is valid or not.
             *
             * @param {string} validationName
             * @param {string} validatorName
             * @param {Object} validationConfiguration
             */
            addValidation: function (validationName, validatorName, validationConfiguration) {
                var validatorInstance = Fz.Validation.getValidator(validatorName);

                if (null === validatorInstance
                    && true === validationConfiguration['settings']['useAjax']
                ) {
                    validatorInstance = Fz.Validation.getValidator('AjaxValidator');
                }

                if (null !== validatorInstance) {
                    validationRules[validationName] = {
                        validator: validatorInstance,
                        configuration: validationConfiguration
                    };

                    // Sorting the rules by priority.
                    var sortedRules = [];

                    for (validationName in validationRules) {
                        if (validationRules.hasOwnProperty(validationName)) {
                            var priority = validationRules[validationName]['configuration']['settings'].hasOwnProperty('priority')
                                ? validationRules[validationName]['configuration']['settings']['priority']
                                : 0;
                            sortedRules.push([priority, [validationName, validationRules[validationName]]])
                        }
                    }

                    sortedRules.sort(function (a, b) {
                        return b[0] - a[0]
                    });

                    validationRules = {};

                    for (var i = 0; i < sortedRules.length; i++) {
                        validationRules[sortedRules[i][1][0]] = sortedRules[i][1][1];
                    }
                } else {
                    Fz.debug('The validator "' + validatorName + '" will not be used in JavaScript.', Fz.TYPE_NOTICE);
                }
            },

            /**
             * Adds an activation condition to this validation service. There
             * can be as much conditions as needed, and if at least one
             * condition returns false, the validation will be deactivated until
             * all conditions return true.
             *
             * The second argument `callback` must be a function, which will be
             * called every time the validation service begins its process. It
             * must return a boolean: false if the service should be
             * deactivated, true otherwise.
             *
             * @param {string}             name     Arbitrary name of the condition.
             * @param {validationCallback} callback Callback function called, see description.
             */
            addActivationCondition: function (name, callback) {
                eventsManager.on('activationCondition', callback);
            },

            /**
             * Adds an activation condition to a specific validation rule of
             * this service. There can be as much conditions as needed, and if
             * at least one condition returns false, the validation will be
             * deactivated for the validation rule, until all conditions return
             * true.
             *
             * The second argument `callback` must be a function, which will be
             * called every time the validation rule is checked. It must return
             * a boolean: false if the service should be deactivated, true
             * otherwise.
             *
             * @param {string}   name          Arbitrary name of the condition.
             * @param {string}   validationName Name of the validation rule which will be bound to this condition.
             * @param {Function} callback      Callback function called, see description.
             */
            addActivationConditionForValidator: function (name, validationName, callback) {
                eventsManager.on('activationConditionForValidator.' + validationName, callback);
            },

            /**
             * Event handler for when the validation process begins.
             *
             * @param {Function} callback
             */
            onValidationBegins: function (callback) {
                eventsManager.on('validationBegins', callback);
            },

            /**
             * Event handler for when the validation process is done.
             *
             * The callback function gets a `result` parameter, containing the
             * instance of the validation result.
             *
             * @param {validationResultCallback} callback
             */
            onValidationDone: function (callback) {
                eventsManager.on('validationDone', callback);
            },

            /**
             * Even handler for when a specific error occurs on the field.
             *
             * If the parameter `errorName` is null, if any error is found for
             * the validation `validationName`, the callback function will run.
             *
             * @param {String}   validationName
             * @param {String}   errorName
             * @param {Function} callback
             */
            onError: function (validationName, errorName, callback) {
                var eventName = (null === errorName)
                    ? 'validationError.' + Fz.camelCaseToDashed(validationName)
                    : 'validationError.' + Fz.camelCaseToDashed(validationName) + '.' + Fz.camelCaseToDashed(errorName);

                eventsManager.on(eventName, callback);
            },

            /**
             * Will check all the validation condition of this field. If they
             * all return true, then `runValidationCallback` is called.
             *
             * @param {Function} runValidationCallback  The callback function called if the validation should run.
             * @param {Function} stopValidationCallback The callback function called if the validation should be cancelled.
             */
            checkActivationCondition: function (runValidationCallback, stopValidationCallback) {
                checkConditionInternal(
                    eventsManager.getCallbacksForEvent('activationCondition'),
                    runValidationCallback,
                    stopValidationCallback
                );
            },

            /**
             * Will check all the validation condition of this field for the
             * given validator. If they all return true, then
             * `runValidationCallback` is called.
             *
             * @param {string}   validatorName          Name of the validator.
             * @param {Function} runValidationCallback  The callback function for if the validation should run.
             * @param {Function} stopValidationCallback The callback function for if the validation should stop.
             */
            checkActivationConditionForValidator: function (validatorName, runValidationCallback, stopValidationCallback) {
                var conditions = eventsManager.getCallbacksForEvent('activationConditionForValidator.' + validatorName);
                if (conditions.length === 0) {
                    runValidationCallback();
                } else {
                    checkConditionInternal(conditions, runValidationCallback, stopValidationCallback);
                }
            },

            /**
             * Will handle the situation where a given field was hidden (not
             * activated anymore), then activated again. During the time it is
             * not activated, all the data attributes of the form element must
             * be removed, to be restored when the field is activated again. It
             * is mainly used to prevent wrong CSS behaviours.
             *
             * The same behaviour is done if the last validation process
             * returned an error: the rule which returned this error may be
             * deactivated since then.
             *
             * Will apply only when the field was validated at least once.
             */
            refreshValidation: function () {
                if (true === wasValidated) {
                    var self = this;

                    var stopValidationCallBack = function () {
                        deactivate();
                    };

                    var endingCallBack = function () {
                        if (getStringValue() !== lastValidatedValue) {
                            self.validate();
                        } else {
                            activate();
                        }
                    };

                    var launchValidation = function () {
                        self.validate();
                    };

                    var continueCallback = function () {
                        if (null === lastValidationErrorName) {
                            self.checkCurrentActivatedValidators(endingCallBack, launchValidation);
                        } else {
                            self.checkActivationConditionForValidator(lastValidationErrorName, endingCallBack, launchValidation);
                        }
                    };

                    this.checkActivationCondition(continueCallback, stopValidationCallBack);
                }
            },

            /**
             * Returns the last errors returned by the validators of this
             * service.
             *
             * Please be aware that the usage of this function is useful only
             * when the validation did actually run, so you should always use
             * this function after having checked the result of the function
             * `wasValidated()`, or in a callback of `onValidationDone()`.
             *
             * @returns {Object.<Object>}
             */
            getErrors: function () {
                return errors;
            },

            /**
             * @see getErrors()
             *
             * @returns {Object.<Object>}
             */
            getWarnings: function () {
                return warnings;
            },

            /**
             * @see getErrors()
             *
             * @returns {Object.<Object>}
             */
            getNotices: function () {
                return notices;
            },

            /**
             * Returns the last validator name which caused an error.
             *
             * @returns {?string}
             */
            getLastValidationErrorName: function () {
                return lastValidationErrorName;
            },

            /**
             * This function will check the differences between the list of
             * current activated validation rules, and the list of activated
             * validation rules during the last validation process run.
             *
             * If no differences are found, `defaultCallback` is called,
             * otherwise `differencesCallback` is called.
             *
             * This is no public API, don't use in your own scripts please!
             *
             * @param {Function} defaultCallback
             * @param {Function} differencesCallback
             */
            checkCurrentActivatedValidators: function (defaultCallback, differencesCallback) {
                this.getActivatedValidationRules(function (activatedValidations) {
                    var flag = true;
                    var activatedValidationsNumber = Fz.objectSize(activatedValidations);

                    if ('*' !== validatorsCheckedDuringLastValidation
                        && activatedValidationsNumber === validatorsCheckedDuringLastValidation.length
                        && activatedValidationsNumber > 0
                    ) {
                        for (var validationName in activatedValidations) {
                            if (activatedValidations.hasOwnProperty(validationName)) {
                                if (-1 === validatorsCheckedDuringLastValidation.indexOf(validationName)) {
                                    flag = false;
                                    break;
                                }
                            }
                        }
                    }

                    if (false === flag) {
                        differencesCallback();
                    } else {
                        defaultCallback();
                    }
                });
            },

            /**
             * Returns true if the field is currently being validated.
             *
             * @returns {boolean}
             */
            isValidating: function () {
                return validationRunning;
            }
        };

        return serviceInstance;
    };

    return {
        /**
         * @param {Object}                      states
         * @param {Formz.FullField}             states.field
         * @param {Object}                      states.existingErrors
         * @param {Object}                      states.existingWarnings
         * @param {Object}                      states.existingNotices
         * @param {string}                      states.submittedFieldValue
         * @param {boolean}                     states.wasValidated
         * @param {boolean}                     states.isDeactivated
         * @param {Formz.EventsManagerInstance} states.eventsManager
         * @returns {Formz.Field.ValidationServiceInstance}
         */
        getFieldValidationService: function (states) {
            var valueCallback = function () {
                return states.field.getValue();
            };

            var validationDataCallback = function () {
                return {field: states.field};
            };

            return servicePrototype({
                valueCallback: valueCallback,
                validationDataCallback: validationDataCallback,
                eventsManager: states.eventsManager,
                defaultErrorMessage: states.field.getForm().getConfiguration()['settings']['defaultErrorMessage'],
                existingErrors: states.existingErrors,
                existingWarnings: states.existingWarnings,
                existingNotices: states.existingNotices,
                submittedFieldValue: states.submittedFieldValue,
                wasValidated: states.wasValidated,
                isDeactivated: states.isDeactivated
            });
        }
    }
})();
