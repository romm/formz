.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _usersManual-tips:

Tips
====

Factorize configuration
^^^^^^^^^^^^^^^^^^^^^^^

TypoScript has a very useful feature when it comes to manipulate several configuration parts, that have a chance to be used more than once. Indeed, it's possible to inject, at any place in the TypoScript tree, a whole existing configuration: simply use the ``<`` operator followed by the path to the existing configuration.

It is strongly advised to inject already existing configuration in other TypoScript properties (see :ref:`usersManual-typoScript-configurationValidators`, :ref:`usersManual-typoScript-configurationBehaviours` and :ref:`usersManual-typoScript-configurationFields`), rather than writing the whole needed configuration every time.

**Bad usage example:**

.. code-block:: typoscript

    config.tx_formz.forms {
        MyVendor\MyExtension\Form\ExampleForm {
            fields {
                email {
                    validation {
                        isEmail {
                            className = MyVendor\MyExtension\Validation\Validator\IsEmail
                            messages.default.value = Provide a valid email address.
                        }
                        required {
                            className = Romm\Formz\Validation\Validator\Required
                        }
                    }
                    behaviours {
                        toLowerCase {
                            className = Romm\Formz\Behaviours\ToLowerCase
                        }
                    }
                }
            }
        }

        MyVendor\MyExtension\Form\SecondExampleForm {
            fields {
                email {
                    validation {
                        isEmail {
                            className = MyVendor\MyExtension\Validation\Validator\IsEmail
                            messages.default.value = Provide a valid email address.
                        }

                        required {
                            className = Romm\Formz\Validation\Validator\Required
                        }
                    }
                }
            }
        }
    }

**Good usage example:**

.. code-block:: typoscript

    config.tx_formz {
        validators {
            isEmail {
                className = MyVendor\MyExtension\Validation\Validator\IsEmail
                messages.default.value = Provide a valid email address.
            }
        }

        behaviours {
            toLowerCase {
                className = Romm\Formz\Behaviours\ToLowerCase
            }
        }

        forms {
            MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        validation {
                            isEmail < config.tx_formz.validators.isEmail
                            required < config.tx_formz.validators.required
                        }
                        behaviours {
                            toLowerCase < config.tx_formz.behaviours.toLowerCase
                        }
                    }
                }
            }

            MyVendor\MyExtension\Form\SecondExampleForm {
                fields {
                    email {
                        validation {
                            isEmail < config.tx_formz.validators.isEmail
                            required < config.tx_formz.validators.required
                        }
                    }
                }
            }
        }
    }
