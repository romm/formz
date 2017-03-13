/**
 * Contains utilities managing the form behaviours when it is submitted.
 *
 * Basically, when a form is submitted, a check is processed on every field, to
 * see if they are valid. If at least one field is not, the form submission is
 * cancelled.
 */
Fz.Form.SubmissionService = (function () {
    /**
     * @param {string} formName
     */
    var submissionServicePrototype = function (formName) {
        /**
         * @type {boolean}
         */
        var submissionBeingChecked = false;

        /**
         * @type {Object}
         */
        var fieldsChecked = {};

        /**
         * @type {Object<Formz.FieldInstance>}
         */
        var fieldsCheckedWithErrors = {};

        /**
         * @type {boolean}
         */
        var formCanBeSubmitted = true;

        /**
         * @type {number}
         */
        var fieldsCheckedNumber = 0;

        /**
         * @type {Array<function>}
         */
        var onSubmitCallBacks = [];

        Fz.Form.get(formName, function(form) {
            // Detecting the form submission.
            form.getElement().addEventListener(
                'submit',
                function (e) {
                    handleFormSubmission(e)
                },
                false
            );

            // Looping on every field, to plug a check for this service on their validation process.
            var fields = form.getFields();
            for (var fieldName in fields) {
                if (fields.hasOwnProperty(fieldName)) {
                    (function (name) {
                        fields[name].onValidationDone(function () {
                            if (true === submissionBeingChecked) {
                                checkAllFieldsAreValid();
                            }
                        });
                    })(fieldName);
                }
            }

            /**
             * Callback function called when the form was submitted. The submission
             * is cancelled and a check is done on every field to know if the
             * submission should be launched for real.
             *
             * @param e
             * @returns {boolean}
             */
            var handleFormSubmission = function (e) {
                e.preventDefault();

                try {
                    if (false === submissionBeingChecked) {
                        submissionBeingChecked = true;
                        formCanBeSubmitted = true;
                        fieldsChecked = {};
                        fieldsCheckedWithErrors = {};
                        fieldsCheckedNumber = 0;

                        form.getElement().setAttribute('fz-submission-done', '1');
                        form.getElement().setAttribute('fz-loading', '1');

                        checkAllFieldsAreValid();
                    }
                } catch (e) {
                    Fz.debug('Error during submission: "' + e + '".', Fz.TYPE_ERROR);

                    form.getElement().submit();
                }

                return false;
            };

            /**
             * Internal function which will actually do all the work of checking the
             * fields, see if they are all valid, and cancel the whole form
             * submission if at least one field is not valid.
             */
            var checkAllFieldsAreValid = function () {
                var fieldsCheckedTotal = 0;

                /**
                 * This is the final function called when a field has been entirely
                 * checked. If all the fields have been checked, then the final
                 * process runs, depending on if errors were found or not.
                 */
                var endProcess = function () {
                    if (fieldsCheckedNumber === fieldsCheckedTotal) {
                        submissionBeingChecked = false;
                        form.getElement().removeAttribute('fz-loading');

                        if (false === formCanBeSubmitted) {
                            Fz.debug('Submission cancelled because the following fields are not valid: ' + Fz.objectKeys(fieldsCheckedWithErrors).join(', ') + '.', Fz.TYPE_NOTICE);

                            scrollToFirstNotValidElement();
                        } else {
                            /*
                             * The form shall be submitted, however we call the
                             * third-party `onSubmit` callbacks (if at least one
                             * is defined), which can cancel the form submission
                             * by returning `false`.
                             */
                            var submitFlag = true;

                            for (var i = 0; i < onSubmitCallBacks.length; i++) {
                                var testResult = onSubmitCallBacks[i]();
                                if (false === testResult) {
                                    submitFlag = false;
                                    break;
                                }
                            }

                            if (true === submitFlag) {
                                form.getElement().setAttribute('fz-submitted', '1');

                                // Everything ran perfectly for the form: the real submission is launched.
                                form.getElement().submit();
                            }
                        }
                    }
                };

                /**
                 * Internal function called to flag the given field as "fully
                 * checked".
                 *
                 * @param {string} fieldName
                 */
                var checkField = function (fieldName) {
                    fieldsCheckedNumber++;
                    fieldsChecked[fieldName] = true;
                    endProcess();
                };

                var fields = form.getFields();
                for (var fieldName in fields) {
                    if (fields.hasOwnProperty(fieldName)) {
                        fieldsCheckedTotal++;
                    }
                }

                if (0 === fieldsCheckedTotal) {
                    endProcess();
                } else {
                    /**
                     * Callback function used to process a field validation if it is
                     * activated at this moment.
                     *
                     * @param {Formz.FullField} field
                     */
                    var callBackFieldActivated = function (field) {
                        var fieldName = field.getName();

                        if (false === field.wasValidated()) {
                            field.validate();
                        } else if (false === field.isValidating()) {
                            if (true === field.isValid()) {
                                field.checkCurrentActivatedValidators(
                                    function() {
                                        checkField(fieldName);
                                    },
                                    function() {
                                        field.validate();
                                    }
                                );
                            } else {
                                /*
                                 * If the field was not valid after his last
                                 * validation check, we check if the last validation
                                 * rule which returned an error is still activated.
                                 */
                                field.checkActivationConditionForValidator(
                                    field.getLastValidationErrorName(),
                                    function () {
                                        // The last validation error is still activated, the form can not be submitted.
                                        fieldsCheckedWithErrors[fieldName] = field;
                                        formCanBeSubmitted = false;
                                        checkField(fieldName);
                                    },
                                    function() {
                                        field.validate();
                                    }
                                );
                            }
                        }
                    };

                    for (fieldName in fields) {
                        if (fields.hasOwnProperty(fieldName)) {
                            if (false === (fieldName in fieldsChecked)) {
                                (function (fieldName) {
                                    fields[fieldName].checkActivationCondition(
                                        function () {
                                            callBackFieldActivated(fields[fieldName])
                                        },
                                        function () {
                                            checkField(fieldName);
                                        }
                                    );
                                })(fieldName);
                            }
                        }
                    }
                }
            };

            /**
             * Function called when at least an error was found during the fields
             * validation. A loop will be done to determine which field is the
             * highest on the screen, and the window will scroll to show this field,
             * to indicate the submission was cancelled because of its error.
             */
            var scrollToFirstNotValidElement = function () {
                var top, left;
                for (fieldName in fieldsCheckedWithErrors) {
                    if (fieldsCheckedWithErrors.hasOwnProperty(fieldName)) {
                        var element = fieldsCheckedWithErrors[fieldName].getElements()[0];
                        var bodyScrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                        var bodyScrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
                        var rect = element.getBoundingClientRect();
                        var tmpTop = rect.top + bodyScrollTop;

                        if (!top || tmpTop < top) {
                            top = tmpTop;
                            left = rect.left + bodyScrollLeft;
                        }
                    }
                }

                window.scrollTo(left, top - 50);
            };
        });

        /**
         * @namespace Formz.Form.SubmissionServiceInstance
         * @typedef {Formz.Form.SubmissionServiceInstance} Formz.Form.SubmissionServiceInstance
         */
        return {
            /**
             * Use this function if you need to run a script when the form
             * submission is actually running, and was not cancelled because of
             * invalid fields.
             *
             * @param {Function} callback
             */
            onSubmit: function (callback) {
                if (typeof callback === 'function') {
                    onSubmitCallBacks.push(callback);
                }
            }
        };
    };

    /** @namespace Formz.Form.SubmissionService */
    return {
        /**
         * Creates an instance of the submission service, for a given form.
         *
         * @param {string} formName
         */
        get: function (formName) {
            return submissionServicePrototype(formName);
        }
    };
})();
