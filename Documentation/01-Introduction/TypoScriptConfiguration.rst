.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _typoScriptConfiguration:

TypoScript configuration
========================

A big part of the set up and configuration of a form will be handled with TypoScript.

You can access the list of available properties in the chapter “:ref:`usersManual-typoScript`”

Below is a preview of what a fully-working form configuration looks like, with an example of a form with five fields:

.. code-block:: typoscript

    config.tx_formz {
        forms {
            # Setting up the form.
            Romm\FormzTemplate\Form\ExampleForm {
                # Activation conditions which can be used by fields.
                activationCondition {
                    # Condition validated when the user does have a certificate.
                    hasCertificate {
                        type = fieldHasValue
                        fieldName = hasCertificate
                        fieldValue = 1
                    }
                }

                # Fields set up.
                fields {
                    # Field: email
                    email {
                        # Required + must be a valid mail address.
                        validation {
                            required < config.tx_formz.validators.required
                            isEmail < config.tx_formz.validators.email
                        }

                        # Turns the value in lower case.
                        behaviours {
                            toLowerCase < config.tx_formz.behaviours.toLowerCase
                        }
                    }

                    # Field: name
                    name {
                        # Required.
                        validation {
                            required < config.tx_formz.validators.required
                        }
                    }

                    # Field: first name
                    firstName {
                        # Required.
                        validation {
                            required < config.tx_formz.validators.required
                        }
                    }

                    # Field: has a certificate
                    hasCertificate {
                        # Required.
                        validation {
                            required < config.tx_formz.validators.required
                        }
                    }

                    # Field: name of the certificate
                    certificateName {
                        # Required.
                        validation {
                            required < config.tx_formz.validators.required
                        }

                        # Activated when the user has a certificate.
                        activation {
                            condition = hasCertificate
                        }
                    }
                }
            }
        }
    }
