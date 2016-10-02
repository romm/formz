Formz.Condition = (function () {
    /**
     * @callback Formz.Condition.ConditionCallback
     * @param {Formz.FormInstance} form
     * @param {Object}             data
     */

    /**
     * @param {Object}                            states
     * @param {Formz.Condition.ConditionCallback} states.callback
     * @returns {Formz.Condition}
     */
    var conditionPrototype = function (states) {
        /**
         * @type {Formz.Condition.ConditionCallback}
         */
        var callback = states.callback;

        /**
         * @namespace Formz.Condition
         * @typedef {Formz.Condition} Formz.Condition
         */
        return {
            /**
             * Call this function to check this condition with the given values.
             *
             * @param {Formz.FormInstance} form
             * @param {Object}             data
             */
            validate: function (form, data) {
                return callback(form, data);
            }
        }
    };

    /**
     * Contains all conditions instances.
     *
     * @type {Object<Formz.Condition>}
     */
    var conditions = {};

    /** @namespace Formz.Condition */
    return {
        /**
         * Registers a condition.
         *
         * @param {string}                            name
         * @param {Formz.Condition.ConditionCallback} callback
         */
        registerCondition: function (name, callback) {
            conditions[name] = conditionPrototype({callback: callback});
        },

        /**
         *
         * @param {string}             name
         * @param {Formz.FormInstance} form
         * @param {Object}             data
         */
        validateCondition: function (name, form, data) {
            var result = true;

            if (this.hasCondition(name)) {
                result = this.getCondition(name).validate(form, data);
            } else {
                Formz.Debug.debug('Trying to validate a non-existing condition: "' + name + '".', Formz.TYPE_ERROR);
            }

            return result;
        },

        /**
         * Gets a condition by a given name.
         *
         * @param {string} name
         * @returns {?Formz.Condition}
         */
        getCondition: function (name) {
            return (name in conditions)
                ? conditions[name]
                : null;
        },

        /**
         * Is the condition registered?
         *
         * @param {string} name
         * @returns {boolean}
         */
        hasCondition: function (name) {
            return (name in conditions);
        }
    };
})();
