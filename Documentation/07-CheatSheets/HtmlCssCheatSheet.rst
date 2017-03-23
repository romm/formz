.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _cheatSheets-css:

HTML/CSS cheat-sheet
====================

PhpStorm auto-completion
------------------------

FormZ provides a schema allowing IDEs like PhpStorm to give auto-completion for its ViewHelpers in Fluid templates.

You can find a guide on integrating XSD files in your IDE here: https://github.com/FluidTYPO3/schemaker/blob/development/README.md#how-to-use-xsd-in-ide

**Example:**

.. code-block:: html

    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns:fz="http://typo3.org/ns/Romm/Formz/ViewHelpers">

        <f:layout name="MyLayout" />

        <f:section name="MySection">
            <fz:form name="myForm">
                ...
            </fz:form>
        </f:section>

    </html>


“data” attributes
-----------------

List of attributes used on the tag ``<form>`` and which can be used with CSS:


* ``fz-valid``: when all fields are valid.

* ``fz-value-{field-name}="value"``: current value of the field;

* ``fz-valid-{field-name}="1"``: the field is valid (no validation error);

* ``fz-error-{field-name}="1"``: the field has at least one error;

* ``fz-error-{field-name}-{rule-name}-{message-key}``: the field has the error ``{rule-name}`` with the message ``{message-key}`` ;

* ``fz-submission-done``: the form has been submitted;

* ``fz-submitted``: the form is being submitted.

**Example of CSS:**

In the example below, the block ``info-customer`` is hidden as long as the button “No” is not selected. It's the same as saying that it will be displayed only if the button “No” is selected.

.. code-block:: css

    form[name="myForm"]:not([fz-value-is-customer="0"]) .info-customer {
        display: none;
    }

.. code-block:: html

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form name="myForm" action="submitForm">
        <fz:field name="isCustomer" layout="default">
            <fz:slot name="Field">
                Are you a customer?
                <br />
                <f:form.radio property="isCustomer" value="1" />&nbsp;Yes
                <br />
                <f:form.radio property="isCustomer" value="0" />&nbsp;No
            </fz:slot>
        </fz:field>

        <div class="info-customer">
            You can subscribe easily to our service <a href="...">by clicking here</a>.
        </div>
    </fz:form>

Loading behaviour
-----------------

* The attribute ``fz-loading`` is added:

  1. To the tag ``<form>`` when the form is being **validated**. It can be used for instance to deactivate the submission button, to wait for the fields to be validated.

     .. attention::

         Another attribute is used at the **submission** of the form (when all fields are valid), see below.

     **Example:**

     .. code-block:: css

         .formz[fz-loading] input[type="submit"] {
             display: none;
         }

  2. To the field container when it is being validated. It can be used for instance to display a loading circle when an Ajax request is running.

* The attribute ``fz-submitted`` is added to the tag ``<form>`` when the form is being **submitted** (when all fields were validated).
