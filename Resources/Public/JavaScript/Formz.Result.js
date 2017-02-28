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
        var errorMessages = {};

        /**
         * Contains all the warning messages.
         *
         * @type {Object<string>}
         */
        var warningMessages = {};

        /**
         * Contains all the notice messages.
         *
         * @type {Object<string>}
         */
        var noticeMessages = {};

        /**
         * @type {Object}
         */
        var data = {};

        /**
         * @param {Object} states             Object containing informations.
         * @param {string} states.name        Name (key) of the message.
         * @param {string} states.message     Text of the message.
         * @param {Array}  [states.arguments] Arguments to be replaced in the translated message.
         */
        var sanitizeMessageStates = function (states) {
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

            return {
                name: name,
                message: message,
                arguments: arguments
            };
        };

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
                states = sanitizeMessageStates(states);
                errorMessages[states.name] = states.message;
            },

            /**
             * Returns the error messages of the result.
             *
             * @returns {Object<string>}
             */
            getErrors: function () {
                return errorMessages;
            },

            /**
             * Returns true if the result contains errors.
             *
             * @returns {boolean}
             */
            hasErrors: function () {
                return Formz.objectSize(errorMessages) > 0;
            },

            /**
             * Adds a warning to the result.
             *
             * @param {Object} states             Object containing informations.
             * @param {string} states.name        Name (key) of the message.
             * @param {string} states.message     Text of the message.
             * @param {Array}  [states.arguments] Arguments to be replaced in the translated message.
             */
            addWarning: function (states) {
                states = sanitizeMessageStates(states);
                warningMessages[states.name] = states.message;
            },

            /**
             * Returns the warning messages of the result.
             *
             * @returns {Object<string>}
             */
            getWarnings: function () {
                return warningMessages;
            },

            /**
             * Returns true if the result contains warnings.
             *
             * @returns {boolean}
             */
            hasWarnings: function () {
                return Formz.objectSize(warningMessages) > 0;
            },
            
            /**
             * Adds a notice to the result.
             *
             * @param {Object} states             Object containing informations.
             * @param {string} states.name        Name (key) of the message.
             * @param {string} states.message     Text of the message.
             * @param {Array}  [states.arguments] Arguments to be replaced in the translated message.
             */
            addNotice: function (states) {
                states = sanitizeMessageStates(states);
                noticeMessages[states.name] = states.message;
            },

            /**
             * Returns the notice messages of the result.
             *
             * @returns {Object<string>}
             */
            getNotices: function () {
                return noticeMessages;
            },

            /**
             * Returns true if the result contains notices.
             *
             * @returns {boolean}
             */
            hasNotices: function () {
                return Formz.objectSize(noticeMessages) > 0;
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
