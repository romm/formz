Formz.Validation = (function () {
    /**
     * @callback Formz.Validation.ValidatorCallback
     * @param {string}                   value
     * @param {Function}                 callback
     * @param {Object}                   states
     * @param {Formz.ResultInstance}     states.result
     * @param {Object}                   states.data
     * @param {string}                   states.validatorName
     * @param {object}                   states.configuration
     */

    /**
     * @param {Object}                             states
     * @param {Formz.Validation.ValidatorCallback} states.callback
     * @returns {Formz.Validator}
     */
    var validatorPrototype = function (states) {
        /**
         * @type {Formz.Validation.ValidatorCallback}
         */
        var callback = states.callback;

        /**
         * @namespace Formz.Validator
         * @typedef {Formz.Validator} Formz.Validator
         */
        return {
            /**
             * Call this function to validate a value for a given field.
             *
             * @param {String|Object} value
             * @param {Formz.Result}  result
             * @param {Object}        data
             * @param {string}        validationName
             * @param {Object}        validationConfiguration
             * @param {Function}      callbackFunction
             */
            validate: function (value, result, data, validationName, validationConfiguration, callbackFunction) {
                var states = {
                    result: result,
                    configuration: validationConfiguration,
                    data: data,
                    validatorName: validationName
                };

                var validationCallback = function() {
                    callbackFunction(result);
                };

                // Value can be an object, for instance for a multiple checkbox field.
                if (typeof value === 'object') {
                    if (value.length === 0) {
                        callback('', validationCallback, states);
                    } else {
                        var tempValidationCallback = function () {
                            // Empty function...
                        };

                        // If there are several values, they are evaluated one by one.
                        for (var i = 0; i < value.length; i++) {
                            if (i === value.length - 1) {
                                // If this is the last evaluated value, we call the ending callback function.
                                tempValidationCallback = validationCallback;
                            }

                            callback(value[i], tempValidationCallback, states);
                        }
                    }
                } else {
                    callback(value, validationCallback, states);
                }
            }
        }
    };

    /**
     * Contains all validators instances.
     *
     * @type {Object<Formz.Validator>}
     */
    var validators = {};

    /** @namespace Formz.Validation */
    return {
        /**
         * Registers a validator.
         *
         * @param {string}                             name
         * @param {Formz.Validation.ValidatorCallback} callback
         */
        registerValidator: function (name, callback) {
            validators[name] = validatorPrototype({callback: callback});
        },

        /**
         * Gets a validator by a given name.
         *
         * @param {string} name
         * @returns {?Formz.Validator}
         */
        getValidator: function (name) {
            return (name in validators)
                ? validators[name]
                : null;
        }
    };
})();
