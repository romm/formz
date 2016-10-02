Formz.EventsManager = (function () {
    /**
     * @returns {Formz.EventsManagerInstance}
     */
    var prototype = function () {
        var events = {};

        /**
         * @namespace Formz.EventsManagerInstance
         * @typedef {Formz.EventsManagerInstance} Formz.EventsManagerInstance
         */
        return {
            on: function(eventName, callback) {
                if (typeof callback === 'function') {
                    if (false === eventName in events) {
                        events[eventName] = [];
                    }
                    events[eventName].push(callback);
                }
            },
            dispatch: function(eventName, arguments) {
                if (eventName in events) {
                    for (var callback in events[eventName]) {
                        if (events[eventName].hasOwnProperty(callback)) {
                            events[eventName][callback](arguments);
                        }
                    }
                }
            },
            getCallbacksForEvent: function(eventName) {
                return (eventName in events)
                    ? events[eventName]
                    : [];
            }
        };
    };

    return {
        /**
         * @returns {Formz.EventsManagerInstance}
         */
        get: function() {
            return prototype();
        }
    };
})();
