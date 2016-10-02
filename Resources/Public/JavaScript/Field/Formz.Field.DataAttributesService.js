Formz.Field.DataAttributesService = (function () {
    /**
     * @param {Object}              states
     * @param {Formz.FieldInstance} states.field
     */
    var servicePrototype = function (states) {
        /**
         * @type {Formz.FullField}
         */
        var field = states.field;

        var dataAttributesNames = {
            valid: 'formz-valid-' + Formz.camelCaseToDashed(field.getName()),
            value: 'formz-value-' + Formz.camelCaseToDashed(field.getName()),
            error: 'formz-error-' + Formz.camelCaseToDashed(field.getName()),
            errors: [],
            save: {}
        };

        /**
         * @namespace Formz.Field.DataAttributesServiceInstance
         * @typedef {Formz.Field.DataAttributesServiceInstance} Formz.Field.DataAttributesServiceInstance
         */
        return {
            addValidDataAttribute: function () {
                dataAttributesNames.errors = [];
                field.getForm().getElement().setAttribute(dataAttributesNames.valid, '1');
            },

            removeValidDataAttribute: function () {
                field.getForm().getElement().removeAttribute(dataAttributesNames.valid);
            },

            refreshValueDataAttribute: function () {
                var value = field.getValue();
                var stringValue = value;
                if (typeof value === 'object') {
                    // We need to use a space as a separator, because the CSS tilde selector "~=" does not work with commas.
                    stringValue = value.join(' ');
                }

                field.getForm().getElement().setAttribute(dataAttributesNames.value, stringValue);
            },

            removeValueDataAttribute: function () {
                field.getForm().getElement().removeAttribute(dataAttributesNames.value);
            },

            removeErrorsDataAttributes: function () {
                field.getForm().getElement().removeAttribute(dataAttributesNames.error);

                for (var i = 0; i < dataAttributesNames.errors.length; i++) {
                    field.getForm().getElement().removeAttribute(dataAttributesNames.errors[i]);
                }
            },

            addErrorsDataAttributes: function (errors) {
                dataAttributesNames.errors = [];

                for (var errorName in errors) {
                    if (errors.hasOwnProperty(errorName)) {
                        for (var errorMessageKey in errors[errorName]) {
                            if (errors[errorName].hasOwnProperty(errorMessageKey)) {
                                var dataErrorName = Formz.Field.DataAttributesService.getFieldErrorDataName(field, errorName, errorMessageKey);
                                dataAttributesNames.errors.push(dataErrorName);
                            }
                        }
                    }
                }

                this.refreshErrorsDataAttributes();
            },

            refreshErrorsDataAttributes: function () {
                if (dataAttributesNames.errors.length > 0) {
                    field.getForm().getElement().setAttribute(dataAttributesNames.error, '1');
                }

                for (var i = 0; i < dataAttributesNames.errors.length; i++) {
                    field.getForm().getElement().setAttribute(dataAttributesNames.errors[i], '1');
                }
            },

            saveAllDataAttributes: function () {
                dataAttributesNames.save = {
                    valid: field.isValid(),
                    errors: dataAttributesNames.errors
                };
            },

            hideAllDataAttributes: function () {
                this.saveAllDataAttributes();
                this.removeValueDataAttribute();
                this.removeValidDataAttribute();
                this.removeErrorsDataAttributes();
            },

            restoreAllDataAttributes: function () {
                this.refreshValueDataAttribute();
                dataAttributesNames.errors = dataAttributesNames.save.errors;
                this.refreshErrorsDataAttributes();
                if (true === dataAttributesNames.save.valid) {
                    this.addValidDataAttribute();
                }
            }
        };
    };

    /** @namespace Formz.Field.DataAttributesService */
    return {
        /**
         * @param {Formz.FieldInstance} field
         */
        get: function(field) {
            return servicePrototype({field: field});
        },

        getFieldErrorDataName: function(field, validationName, errorName) {
            return 'formz-error-' + Formz.camelCaseToDashed(field.getName())
                + '-' + Formz.camelCaseToDashed(validationName)
                + '-' + Formz.camelCaseToDashed(errorName);
        }
    }
})();
