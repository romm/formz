.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-formatMessage:

FormatMessage
=============

Allows formatting a message sent by the validation of a field, using the :ref:`fieldsSettings-messageTemplate` configured for this field.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``message``          Instance of the validation message. It should be an instance returned by the Fluid ViewHelper:
                        ``f:form.validationResults``.

``field``               If for any reason you are using this ViewHelper inside the ViewHelper :ref:`integratorManual-viewHelpers-field`,
                        you can fill the argument ``field`` with the name of the field which will be bound to this class.

                        It must be a valid field name for the current form.
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 5

        {namespace formz=Romm\Formz\ViewHelpers}

        <f:form.validationResults for="{formName}.{fieldName}">
            <f:for each="{validationResults.errors}" iteration="iteration" as="error">
                <formz:formatMessage message="{error}" />
            </f:for>
        </f:form.validationResults>
