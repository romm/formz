config.tx_formz {
    settings {
        # Default class name of the backend cache used by Formz.
        defaultBackendCache = TYPO3\CMS\Core\Cache\Backend\FileBackend

        # Is only used to check if this TypoScript is included on a page which needs it.
        typoScriptIncluded = 1

        # Default settings for the forms.
        #
        # Each one of these settings can be overridden in any form. Example:
        #   config.tx_formz.defaultFormsSettings.defaultClass = formz
        #   can be overridden with:
        #   config.tx_formz.forms.My\Custom\Form.settings.defaultClass = my-form
        defaultFormSettings {
            # Will give this class to all the forms which are created via the form view helper of this extension.
            # You are free to override it for your needs.
            defaultClass = formz

            # This is the default error message which will be used for an error on a field if no message is returned.
            # Can be a LLL-type key.
            defaultErrorMessage =
        }

        # Default settings for the fields.
        #
        # Each one of these settings can be overridden in any single field. Example:
        #   `config.tx_formz.defaultFieldSettings.fieldContainerSelector = [formz-field-container="#FIELD#"]`
        # can be overridden for the field `myField` with:
        #   `config.tx_formz.forms.My\Custom\Form.fields.myField.settings.fieldContainerSelector = .my-field`
        defaultFieldSettings {
            # This is the CSS selector which is used to select the field container of a field.
            # The marker `#FIELD#` is replaced with the name of the field.
            fieldContainerSelector = [formz-field-container="#FIELD#"]

            # This is the CSS selector which is used to select the feedback container of a field.
            # This container is shown whenever an error is returned by the field validation.
            # The marker `#FIELD#` is replaced with the name of the field.
            feedbackContainerSelector = [formz-field-feedback-container="#FIELD#"]

            # This is the CSS selector which is used to select the feedback list container of a field.
            # This container will be directly filled with the error messages, so it should not contain any static content as it would be erased eventually.
            # The marker `#FIELD#` is replaced with the name of the field.
            feedbackListSelector = [formz-field-feedback-list="#FIELD#"]

            # This is the HTML template of an error message, which will be used for every message of the field.
            # The following markers can be used:
            #   `#FIELD#` : Name of the field;
            #   `#FIELD_ID#` : Value of the `id` attribute of the field DOM element;
            #   `#VALIDATOR#` : the validation name which returned the message;
            #   `#KEY#` : the key of the message, usually "default";
            #   `#TYPE#` : the type of the message, usually "error";
            #   `#MESSAGE#` : the actual text of the message.
            messageTemplate = <span class="js-validation-rule-#VALIDATOR# js-validation-type-#TYPE# js-validation-message-#KEY#">#MESSAGE#</span>
        }
    }
}