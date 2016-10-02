Formz.Result = (function () {
    /**
     * @param {Object} states
     * @param {String} states.defaultErrorMessage
     *
     * @returns {Formz.ResultInstance}
     */
    var resultPrototype = function (states) {
        /**
         * Default error message used if no valid message is found for an error.
         *
         * @type {String}
         */
        var defaultErrorMessage = states.defaultErrorMessage || 'Error';

        /**
         * Contains all the errors messages.
         *
         * @type {Object<string>}
         */
        var errorsMessages = {};

        /**
         * @type {Object}
         */
        var data = {};

        /**
         * @namespace Formz.ResultInstance
         * @typedef {Formz.ResultInstance} Formz.ResultInstance
         */
        return {
            /**
             * Adds an error to the result.
             *
             * @param {Object} states             Object containing informations.
             * @param {string} states.name        Name (key) of the message.
             * @param {string} states.message     Text of the message.
             * @param {Array}  [states.arguments] Arguments to be replaced in the translated message.
             */
            addError: function (states) {
                var name = states.name || 'default';
                var message = states.message || '';
                var arguments = states.arguments || [];

                if (null === message
                    || typeof message === 'undefined'
                ) {
                    message = '';
                }

                message = message.toString();

                if ('' === message) {
                    message = defaultErrorMessage;
                }

                message = Formz.Localization.getLocalization(message);

                for (var i = 0; i < arguments.length; i++) {
                    message = message.replace('{' + i + '}', arguments[i].toString());
                }

                errorsMessages[name] = message;
            },

            /**
             * Returns the errors messages of the result.
             *
             * @returns {Object<string>}
             */
            getErrors: function () {
                return errorsMessages;
            },

            /**
             * Returns true if the result contains errors.
             *
             * @returns {boolean}
             */
            hasErrors: function () {
                return Formz.objectSize(errorsMessages) > 0;
            },

            /**
             * @param {Object} newData
             */
            setData: function (newData) {
                data = newData;
            },

            /**
             * @param {string} key
             * @returns {*}
             */
            getDataValue: function (key) {
                return (typeof key === 'string' && key in data)
                    ? data[key]
                    : null;
            }
        };
    };

    return {
        /**
         * @param {Object} states
         * @param {String} states.defaultErrorMessage
         *
         * @returns {Formz.ResultInstance}
         */
        get: function (states) {
            return resultPrototype(states);
        }
    };
})();
