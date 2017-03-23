/**
 * Contains functions to handle translations.
 *
 * This is used for instance by the field validation service: the messages used
 * by the validation rules are fetched directly in this library.
 */
Fz.Localization = (function () {
    /**
     * Contains all the real translations, bound to their hashed index.
     *
     * The indexes are hashed to prevent the same translation text to be
     * registered twice.
     *
     * @type {Object}
     */
    var translations = {};

    /**
     * Contains the keys of the translations, and the hashed index of the true
     * value stored in `translations`.
     *
     * @type {Object}
     */
    var translationsBinding = {};

    return {
        /**
         * Registers translations which will be stored in the variables
         * `translations` and `translationsBinding` (see their description for
         * more details).
         */
        addLocalization: function (translationsValues, translationsBindingValues) {
            for (var key in translationsValues) {
                if (translationsValues.hasOwnProperty(key)) {
                    translations[key] = translationsValues[key];
                }
            }

            for (key in translationsBindingValues) {
                if (translationsBindingValues.hasOwnProperty(key)) {
                    translationsBinding[key] = translationsBindingValues[key];
                }
            }
        },

        /**
         * Get the translated text for the given key.
         */
        getLocalization: function (key) {
            var result = key;

            if (translationsBinding.hasOwnProperty(key)) {
                var translationKey = translationsBinding[key];

                if (translations.hasOwnProperty(translationKey)) {
                    result = translations[translationKey];
                }
            }

            return result;
        }
    };
})();
