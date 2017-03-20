.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _cheatSheets-typoScript:

TypoScript cheat-sheet
======================

Below is a complete tree which displays almost the totality of the provided TypoScript configurations.

You can find details of the properties at the chapter “:ref:`usersManual-typoScript`”.

-----

.. code-block:: typoscript

    # The configuration root is `config.tx_formz`.
    config.tx_formz {
        settings {
            # List of default parameters for forms.
            defaultFormSettings {
                # This CSS class will be given by default to the tag `<form>` of
                # every form.
                defaultClass = test-class

                # Error message used by default after the validation of a field,
                # if none is found.
                defaultErrorMessage = LLL:EXT:extension/.../locallang.xlf:default_error
            }

            # List of parameters given by default to the form fields.
            defaultFieldSettings {
                # Selector of the field HTML container.
                fieldContainerSelector = .fz-field-#FIELD#

                # Selector of the messages HTML container.
                messageContainerSelector = .fz-messages-#FIELD#

                # Selector of the messages list HTML container.
                messageListSelector = .fz-messages-list-#FIELD#

                # HTML template used by the messages returned by the field
                # validation.
                messageTemplate = <span class="fz-message-#TYPE#">#MESSAGE#</span>
            }
        }

        # Here is the list of forms using FormZ.
        forms {
            # The key is the name of the PHP class of the form model.
            \Vendor\Extension\Form\MyForm {
                # List of conditions which can be used by fields and validation
                # rules.
                activationCondition {
                    # This condition is verified when the field `password`
                    # contains a valid value.
                    passwordIsValid {
                        type = fieldIsValid
                        fieldName = passWord
                    }
                }

                # List of the form fields. Each property below must also be set
                # in the PHP class.
                fields {
                    # The key is the name of the field.
                    email {
                        # List of validation rules bounded to this field.
                        validation {
                            # The field is required. This rule has already been
                            # defined, we may then get it with the proper
                            # TypoScript path.
                            required < config.tx_formz.validators.required
                            required.messages {
                                # We can override the error message returned if
                                # the field did not pass the validation.
                                default.value = The email field is required!
                            }

                            # A second rule: the entered value must be a valid
                            # email address.
                            isEmail < config.tx_formz.validators.email
                            isEmail.messages {
                                # Overriding the message. Note that above we
                                # used `value` for a fixed message, here we use
                                # a localization reference.
                                default {
                                    key = error_email
                                    extension = my_extension
                                }
                            }
                        }
                    }

                    # A password field, which is required and must contain at
                    # least 8 characters.
                    password {
                        validation {
                            required < config.tx_formz.validators.required

                            stringLength < required < config.tx_formz.validators.stringLength
                            stringLength.options {
                                minimum = 8
                                maximum = 128
                            }
                        }
                    }

                    # A repeat password field. It is activated only when the
                    # password is valid, and must contain the exact same value.
                    passwordRepeat {
                        validation {
                            required < config.tx_formz.validators.required

                            isSamePassword < config.tx_formz.validators.equalsToField
                            isSamePassword.options {
                                field = password
                            }
                        }

                        # The field is activated only when the field `password`
                        # is valid.
                        activation.expression = passwordIsValid
                    }
                }
            }

            \Vendor\Extension\Form\MyOtherForm {
                # ...
            }
        }
    }
