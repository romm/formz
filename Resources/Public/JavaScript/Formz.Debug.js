Formz.Debug = (function () {
    /**
     * The debug utilities will run only if this variable is set to true.
     *
     * @type {boolean}
     */
    var activated = false;

    /** @namespace Formz.Debug */
    return {
        /**
         * Activates debug utilities.
         */
        activate: function () {
            activated = true;
        },

        /**
         * Deactivates debug utilities.
         */
        deactivate: function () {
            activated = false;
        },

        /**
         * Will show a value in the console.
         *
         * @param {*}      value
         * @param {string} type
         */
        debug: function (value, type) {
            if (activated) {
                var color = '';

                switch (type) {
                    case Formz.TYPE_WARNING:
                        color = 'color: yellow; font-weight:bold;';
                        break;
                    case Formz.TYPE_ERROR:
                        color = 'color: red; font-weight:bold;';
                        break;
                    default:
                        color = 'color: blue; font-weight:bold;';
                        break;
                }

                console.log('%c[Formz - ' + type + '] %c' + value, color, 'color: black;');
            }
        }
    };
})();
