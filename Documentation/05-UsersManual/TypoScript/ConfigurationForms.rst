.. include:: ../../Includes.txt

.. _usersManual-typoScript-configurationForms:

Forms
=====

The configuration for forms is settable at the path ``config.tx_formz.forms``. We can find the list of all forms. Each configuration must use as a key the class name of the form.

Example: ``config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm { ... }``

Properties
----------

You can find below the list of parameters usable by a form.

=============================================================== ============================
Property                                                        Title
=============================================================== ============================
\* :ref:`fields <formFields>`                                   Fields of the form

:ref:`activationCondition <formActivationCondition>`            Activation conditions

:ref:`settings.defaultClass <formDefaultClass>`                 Default class

:ref:`settings.defaultErrorMessage <formDefaultErrorMessage>`   Default error message
=============================================================== ============================

-----

.. _formFields:

Form fields
-----------

.. container:: table-row

    Property
        ``fields``
    Required?
        Yes
    Description
        Contains the list of fields for the form.

        Note that each field must bind a property of the form's PHP model, in order to be processed.

.. _formActivationCondition:

Activation conditions
---------------------

.. container:: table-row

    Property
        ``activationCondition``
    Required?
        No
    Description
        Contains the list of activation conditions which are usable by all the fields of this form. These conditions may then be used in the activation logical expressions of a field (see “:ref:`fieldsActivation-expression`”) or a field validation.

        For more information on this, read the chapter “:ref:`usersManual-typoScript-configurationActivation`”.

        **Example:**

        .. code-block:: typoscript

            activationCondition {
                colorIsRed {
                    type = fieldHasValue
                    fieldName = color
                    fieldValue = red
                }

                colorIsBlue {
                    type = fieldHasValue
                    fieldName = color
                    fieldValue = blue
                }
            }

        .. note::

            Several condition types are available with FormZ core, see chapter “:ref:`usersManual-typoScript-configurationActivation`”.

.. _formDefaultClass:

Default class
-------------

.. container:: table-row

    Property
        ``settings.defaultClass``
    Required?
        No
    Description
        Class given by default to the ``<form>`` tag when using the ViewHelper :php:`Romm\Formz\ViewHelpers\FormViewHelper`.

        The default value is ``formz``.

.. _formDefaultErrorMessage:

Default error message
---------------------

.. container:: table-row

    Property
        ``settings.defaultErrorMessage``
    Required?
        No
    Description
        When an error is bound to a field, if for an unknown reason the error message is empty, the value of this property will be used instead.

        It main contain a LLL reference.
