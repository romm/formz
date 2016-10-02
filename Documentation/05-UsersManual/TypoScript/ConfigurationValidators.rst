.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _usersManual-typoScript-configurationValidators:

Validators
==========

A validator is a rule which will be applied on a field after the form submission.

.. hint::

    By convention, everytime a new common validator is configured, its configuration should be set at the path ``config.tx_formz.validators``; this way, it may be used again by several fields.

Properties
----------

You can find below the list of parameters usable by a validator.

=========================================== =================================
Property                                    Title
=========================================== =================================
\* :ref:`className <validatorsClassName>`   Class name

\* :ref:`options <validatorsOptions>`       Options

:ref:`messages <validatorsMessages>`        Messages

:ref:`activation <validatorsActivation>`    Validator activation
=========================================== =================================

-----

.. _validatorsClassName:

Class name
----------

.. container:: table-row

    Property
        ``className``
    Required?
        Yes
    Description
        Contains the class name used for this validator.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.validators.myRule {
                className = MyVendor\MyExtension\Validator\MyRuleValidator
            }

.. _validatorsOptions:

Validator options
-----------------

.. container:: table-row

    Property
        ``options``
    Required?
        Depends of the validator, some options may be mandatory and cause problems if not filled.
    Description
        Contains the options which will be sent to the validators. These options are predefined in the validator, and must then be known in order to be used correctly.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.validators.numberLength {
                className = NumberLength
                options {
                    # Minimum size.
                    minimum = 0
                    # Maximum size.
                    maximum = 0
                }
            }

        .. note::

            To know the options of a validator, you must read the documentation of its PHP class (see the chapter “:ref:`developerManual-php-validator`”).

.. _validatorsMessages:

Validator messages
------------------

.. container:: table-row

    Property
        ``messages``
    Required?
        No
    Description
        Allows to override the messages of the validator. A validator may have one or several messages, and each one is identified by a key, ``default`` being by convention the default key.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.validators.numberLength {
                messages {
                    # Two message keys: `default` and `test`.
                    default {
                        # Path to the LLL key of the message.
                        key = validator.form.number_length.error
                        # Extension containing the LLL file.
                        extension = formz
                    }
                    test {
                        # If you fill `value`, the value will be directly used
                        # and the system wont try to fetch a translation.
                        value = Message test!
                    }
                }
            }

.. _validatorsActivation:

Validator activation
--------------------

.. container:: table-row

    Property
        ``activation``
    Required?
        No
    Description
        It is possible to activate a validator only in certain cases. The principle is exactly the same of the fields activation, see “:ref:`Conditions d'activation <fieldsActivation-items>`” and “:ref:`Activation du champ <fieldsActivation-condition>`”.

        Example — activating the rule ``required`` of the field ``passwordRepeat`` only when the field ``password`` is valid:

        .. code-block:: typoscript

            passwordRepeat {
                validation {
                    required < config.tx_formz.validators.required
                    required.activation {
                        items {
                            passwordIsValid {
                                type = fieldIsValid
                                fieldName = password
                            }
                        }

                        condition = passwordIsValid
                    }
                }
            }

.. _validatorsUseAjax:

Use Ajax validation
-------------------

.. container:: table-row

    Property
        ``useAjax``
    Required
        No
    Description
        If this property is defined, an Ajax request is sent by JavaScript when it needs to test this validator.

        Note that if a JavaScript version of this validator exists (see “:ref:`developerManual-javaScript-validation-registerValidator`”), then filling this property wont have any effect and the JavaScript validator will be used instead of Ajax.

        .. code-block:: typoscript

            myField {
                validation {
                    serverValidation {
                        className = MyVendor\MyExtension\Validation\Validator\MyValidator
                        useAjax = 1
                    }
                }
            }
