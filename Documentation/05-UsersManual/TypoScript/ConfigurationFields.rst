.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _usersManual-typoScript-configurationFields:

Fields
======

The fields are the properties of a form's PHP model. For each field having a custom behaviour (for instance a validation rule), you must fill its TypoScript configuration.

.. hint::

    By convention, every time a new common field is configured, its configuration should be set at the path ``config.tx_formz.fields``; this way, it may be used again by another form. A good example of a common field is “email”: several forms may use this field with the exact same configuration.

Properties
----------

You can find below the list of parameters usable by a field.

======================================================================================= =====================================
Property                                                                                Title
======================================================================================= =====================================
:ref:`validation <fieldsValidation>`                                                    Field validation rules

:ref:`behaviours <fieldsBehaviours>`                                                    Field behaviours

:ref:`activation.conditions <fieldsActivation-conditions>`                              Activation conditions

:ref:`activation.expression <fieldsActivation-expression>`                              Field activation expression

:ref:`settings.fieldContainerSelector <fieldsSettings-fieldContainerSelector>`          Field container selector

:ref:`settings.messageContainerSelector <fieldsSettings-messageContainerSelector>`      Message container selector

:ref:`settings.messageListSelector <fieldsSettings-messageListSelector>`                Message list container selector

:ref:`settings.messageTemplate <fieldsSettings-messageTemplate>`                        Message template
======================================================================================= =====================================

-----

.. _fieldsValidation:

Field validation
----------------

.. container:: table-row

    Property
        ``validation``
    Required?
        No
    Description
        Contains the list of validators and their rules used for the field's validation.

        Validators will be evaluated following the order of their declaration.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.fields.phoneNumber {
                validation {
                    # The phone number is required.
                    required < config.tx_formz.validators.required

                    # The phone number must have 10 numbers.
                    numberLength < config.tx_formz.validators.numberLength
                    numberLength.options {
                        minimum = 10
                        maximum = 10
                    }
                }
            }

        .. note::

            Note that the validators configurations are fetched directly from ``config.tx_formz.validators``. It prevents a configuration duplication when the validators are used at several places.

.. _fieldsBehaviours:

Field behaviours
----------------

.. container:: table-row

    Property
        ``behaviours``
    Required?
        No
    Description
        Contains the list of behaviours used by the field.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.fields.email {
                behaviours {
                    # Email address is switched to lower case.
                    toLowerCase < config.tx_formz.behaviours.toLowerCase
                }
            }

        .. note::

            Note that the validators configurations are fetched directly from ``config.tx_formz.behaviours``. It prevents a configuration duplication when the behaviours are used at several places.

.. _fieldsActivation-conditions:

Activation conditions
---------------------

.. container:: table-row

    Property
        ``activation.conditions``
    Required?
        No
    Description
        Contains the list of activation conditions which are then usable by this field only. Note that this list will be merged with the one from the property ``activationCondition`` of the form, as its usage is exactly the same: see “:ref:`Conditions d'activation <formActivationCondition>`”.

        **Example:**

        .. code-block:: typoscript

            activation {
                items {
                    colorIsGreen {
                        type = fieldHasValue
                        fieldName = color
                        fieldValue = green
                    }
                }
            }

.. _fieldsActivation-expression:

Field activation
----------------

.. container:: table-row

    Property
        ``activation.expression``
    Required?
        No
    Description
        Contains the field activation condition: a logical expression to describe how the field is activated.

        For more information on this, read the chapter “:ref:`usersManual-typoScript-configurationActivation`”.

        **Example:**

        .. code-block:: typoscript

            activation {
                condition = colorIsRed || colorIsBlue
            }

.. _fieldsSettings-fieldContainerSelector:

Field container selector
------------------------

.. container:: table-row

    Property
        ``settings.fieldContainerSelector``
    Required?
        No
    Description
        Contains the CSS selector which will be used to fetch the container containing the field. For instance, it may be a ``<fieldset>`` element.

        Note that the marker ``#FIELD#`` is dynamically replaced by the name of the field.

        The default value of this parameter is: ``[fz-field-container="#FIELD#"]``.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            fieldContainerSelector = [fz-field-container="#FIELD#"]
                        }
                    }

                    firstName {
                        settings {
                            fieldContainerSelector = .names
                        }
                    }

                    lastName {
                        settings {
                            fieldContainerSelector = .names
                        }
                    }
                }
            }

        .. note::

            You can regroup several fields by assigning them the same container selector, as in the example above.

.. _fieldsSettings-messageContainerSelector:

Message container selector
---------------------------

.. container:: table-row

    Property
        ``settings.messageContainerSelector``
    Required?
        No
    Description
        Contains the CSS selector which will be used to fetch the field message container.

        Note that the marker ``#FIELD#`` is dynamically replaced by the name of the field.

        The default value of this parameter is: ``[fz-field-message-container="#FIELD#"]``.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            messageContainerSelector = #errors-email
                        }
                    }
                }
            }

.. _fieldsSettings-messageListSelector:

Message list selector
---------------------

.. container:: table-row

    Property
        ``settings.messageListSelector``
    Required?
        No
    Description
        Contains the CSS selector which will be used to fetch the block containing the field messages. It's a second selection layout for the message container (``settings.messageContainerSelector``): it allows adding static HTML contents which wont be cleaned up by JavaScript during the message refreshing.

        Note that the marker ``#FIELD#`` is dynamically replaced by the name of the field.

        The default value of this parameter is: ``[fz-field-message-list="#FIELD#"]``.

        If an empty value is set, then the message container will be used.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            messageListSelector =
                        }
                    }
                }
            }

.. _fieldsSettings-messageTemplate:

Message template
----------------

.. container:: table-row

    Property
        ``settings.messageTemplate``
    Required?
        No
    Description
        HTML template used by JavaScript for the messages.

        The default value of this parameter is:

        .. code-block:: html

            <span class="js-validation-rule-#VALIDATOR# js-validation-type-#TYPE#
                  js-validation-message-#KEY#">#MESSAGE#</span>

        In the template, the following values are dynamically replaced:

        * **#FIELD#**: name of the field;

        * **#FIELD_ID#**: “id” attribute of the field. Note that for fields of type “radio” or “checkbox” using this marker is useless.

        * **#VALIDATOR#**: name of the validation rule which sent this message. For instance, it can be ``required``;

        * **#TYPE#**: message type, often an error (in which case the value is ``error``);

        * **#KEY#**: key of the sent message. Most of the time, it will be ``default``;

        * **#MESSAGE#**: the message body.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            messageTemplate = <li class="#TYPE#">#MESSAGE#</li>
                        }
                    }
                }
            }
