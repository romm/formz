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
            warning: 'formz-warning-' + Formz.camelCaseToDashed(field.getName()),
            notice: 'formz-notice-' + Formz.camelCaseToDashed(field.getName()),
            errors: [],
            warnings: [],
            notices: [],
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

            removeMessagesDataAttributes: function () {
                field.getForm().getElement().removeAttribute(dataAttributesNames.error);
                field.getForm().getElement().removeAttribute(dataAttributesNames.warning);
                field.getForm().getElement().removeAttribute(dataAttributesNames.notice);

                for (var i = 0; i < dataAttributesNames.errors.length; i++) {
                    field.getForm().getElement().removeAttribute(dataAttributesNames.errors[i]);
                }
                for (i = 0; i < dataAttributesNames.warnings.length; i++) {
                    field.getForm().getElement().removeAttribute(dataAttributesNames.warnings[i]);
                }
                for (i = 0; i < dataAttributesNames.notices.length; i++) {
                    field.getForm().getElement().removeAttribute(dataAttributesNames.notices[i]);
                }
            },

            addMessagesDataAttributes: function (messages, type) {
                var listKey = type + 's';
                dataAttributesNames[listKey] = [];

                for (var name in messages) {
                    if (messages.hasOwnProperty(name)) {
                        for (var key in messages[name]) {
                            if (messages[name].hasOwnProperty(key)) {
                                var dataMessageName = Formz.Field.DataAttributesService.getFieldMessageDataName(field, type, name, key);
                                dataAttributesNames[listKey].push(dataMessageName);
                            }
                        }
                    }
                }

                this.refreshMessagesDataAttributes(type);
            },

            refreshMessagesDataAttributes: function (type) {
                var listKey = type + 's';

                if (dataAttributesNames[listKey].length > 0) {
                    field.getForm().getElement().setAttribute(dataAttributesNames[type], '1');
                }

                for (var i = 0; i < dataAttributesNames[listKey].length; i++) {
                    field.getForm().getElement().setAttribute(dataAttributesNames[listKey][i], '1');
                }
            },

            saveAllDataAttributes: function () {
                dataAttributesNames.save = {
                    valid: field.isValid(),
                    errors: dataAttributesNames.errors,
                    warnings: dataAttributesNames.warnings,
                    notices: dataAttributesNames.notices
                };
            },

            hideAllDataAttributes: function () {
                this.saveAllDataAttributes();
                this.removeValueDataAttribute();
                this.removeValidDataAttribute();
                this.removeMessagesDataAttributes();
            },

            restoreAllDataAttributes: function () {
                this.refreshValueDataAttribute();
                dataAttributesNames.errors = dataAttributesNames.save.errors;
                dataAttributesNames.warnings = dataAttributesNames.save.warnings;
                dataAttributesNames.notices = dataAttributesNames.save.notices;
                this.refreshMessagesDataAttributes('error');
                this.refreshMessagesDataAttributes('warning');
                this.refreshMessagesDataAttributes('notice');
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

        getFieldMessageDataName: function(field, type, validationName, errorName) {
            return 'formz-' + type + '-' + Formz.camelCaseToDashed(field.getName())
                + '-' + Formz.camelCaseToDashed(validationName)
                + '-' + Formz.camelCaseToDashed(errorName);
        }
    }
})();
