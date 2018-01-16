config.tx_formz {
    ########################
    # AVAILABLE VALIDATORS #
    ########################
    # Note that you can customize the messages for every validator, just check
    # the available messages for a given validator, and set (for example the
    # "default" message):
    #    myValidator.messages {
    #        default {
    #            # Key of the locallang reference. Can be a full path like LLL:EXT:my_ext/.../locallang.xls:my_key
    #            key = path.to.my_message
    #            # Extension containing the locallang.
    #            extension = extension_containing_message
    #            # Static value of the message, not recommended but can be useful sometimes.
    #            value = Hello world!
    #        }
    #    }
    ########################

    validators {
        # REQUIRED
        #   Use to check if a field is not filled.
        #
        #   > Available messages: default
        required {
            className = Romm\Formz\Validation\Validator\RequiredValidator
            priority = 100
        }

        # IS INTEGER
        #   Use to check if a field is a correct integer.
        #
        #   > Available messages: default
        isInteger {
            className = Romm\Formz\Validation\Validator\IsIntegerValidator
        }

        # CONTAINS VALUES
        #   Use to check if a field's value is in a given list.
        #
        #   > Available messages: default
        containsValues {
            className = Romm\Formz\Validation\Validator\ContainsValuesValidator
            options {
                values {
                    # List here all the values that the field can contain.
                }
            }
        }

        # EMAIL
        #   Use to check if a field's value is a valid email address.
        #
        #   > Available messages: default
        email {
            className = Romm\Formz\Validation\Validator\EmailValidator
        }

        # PHONE NUMBER
        # Use to check if a field's value is a valid phone number
        #
        #   > Available messages: default
        phoneNumber {
            className = Romm\Formz\Validation\Validator\NumberLengthValidator
            options {
                minimum = 10
                maximum = 10
            }
            messages {
                default.key = validator.form.phone_number.error
            }
        }

        # NUMBER LENGTH
        #   Use to check the length of a number field. Meaning only numeric characters are accepted.
        #   Example with options: minimum=2, maximum=5
        #     KO: 5
        #     OK: 26
        #     OK: 95483
        #     KO: 456852
        #
        #   > Available messages: default
        numberLength {
            className = Romm\Formz\Validation\Validator\NumberLengthValidator
            options {
                # Minimum length of the field's value.
                minimum = 0
                # Maximum length.
                maximum = 0
            }
        }

        # BETWEEN NUMBERS
        #   Use to check if a number value is contained between two values.
        #   Example with options: minimum=2, maximum=5
        #     KO: 6
        #     OK: 3
        #     OK: 5
        #     KO: 1
        #
        #   > Available messages: default
        betweenNumbers {
            className = Romm\Formz\Validation\Validator\BetweenNumbersValidator
            options {
                # Minimum value.
                minimum = 0
                # Maximum value.
                maximum = 0
            }
        }

        # STRING LENGTH
        #   Use to check the length of a string field.
        #   Example with options: minimum=2, maximum=5
        #     KO: A
        #     OK: AB
        #     OK: ABCD
        #     KO: ABCDEFG
        #
        #   > Available messages: default
        stringLength {
            className = Romm\Formz\Validation\Validator\StringLengthValidator
            options {
                # Minimum length of the field's value.
                minimum = 0
                # Maximum length.
                maximum = 0
            }
        }

        # REGEX
        #   Use to apply a regex check on a value.
        #
        #   > Available messages: default
        regex {
            className = Romm\Formz\Validation\Validator\RegexValidator
            options {
                # Pattern used by the regex (you don't need separators).
                pattern =
                # Options for the regex, e.g. "i" for insensitive case.
                options =
            }
        }

        # WORD
        #   Use to check if a value is a word.
        #
        #   > Available messages: default
        word {
            className = Romm\Formz\Validation\Validator\RegexValidator
            options {
                # Pattern used by the regex (you don't need separators).
                pattern = ^[\w-\' àáâãäåçèéêëìíîïðòóôõöùúûüýÿßÄÖÜ]*$
                # Options for the regex, e.g. "i" for insensitive case.
                options = i
            }
        }

        # WORD Strict
        #   Use to check if a value is a word.
        #
        #   > Available messages: default
        wordStrict {
            className = Romm\Formz\Validation\Validator\RegexValidator
            options {
                # Pattern used by the regex (you don't need separators).
                pattern = ^[A-Za-z0-9àáâãäåçèéêëìíîïðòóôõöùúûüýÿßÄÖÜ]*$
                # Options for the regex, e.g. "i" for insensitive case.
                options = i
            }
        }

        # isNumber
        #   Use to check if a value is a number.
        #
        #   > Available messages: default
        isNumber {
            className = Romm\Formz\Validation\Validator\RegexValidator
            options {
                # Pattern used by the regex (you don't need separators).
                pattern = ^[0-9]*$
            }

            messages.default.key = validator.form.number.error
        }

        # isFloat
        #   Use to check if a value is a number.
        #
        #   > Available messages: default
        isFloat {
            className = Romm\Formz\Validation\Validator\RegexValidator
            options {
                # Pattern used by the regex (you don't need separators).
                pattern = ^[0-9]*\.?[0-9]+$
            }

            messages.default.key = validator.form.number.error
        }


        # EQUALS TO FIELD
        #   Use to check if two fields have the same values.
        #
        #   > Available messages: default
        equalsToField {
            className = Romm\Formz\Validation\Validator\EqualsToFieldValidator
            options {
                # Name of the field which should have the same value as the current field.
                field =
            }
        }
    }
}
