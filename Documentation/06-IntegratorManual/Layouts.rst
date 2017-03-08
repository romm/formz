.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _integratorManual-layouts:

Layouts
=======

During a form integration, one problem always comes back: setting up fields which have **exactly the same template**, meaning the container of a field will be **almost identical from one field to another**. In most cases, two fields answer the exact same HTML/CSS integration rules, which leads to huge code repetition, and breaks the “*Don't Repeat Yourself*” rule.

Problem
-------

Why is block repetition annoying?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

One of the first reasons is obvious: it makes **code reading harder**. It's possible that a field is made out of several HTML tags levels: if we multiply it by the number of fields in a form, we can quickly get to hundreds of lines of code.

Setting up a new field is also slow: adjust the container to the field, and modify all variables bound to the field: label, attributes (``class``, ``id``), etc.

Finally, the **maintainability becomes harder**. Indeed, if a modification must be done in the HTML structure for every field of a form, the correction must be applied on each one of the fields. Worse case: if a site contains dozens of forms based on the same template, every form must be modified.

Solution
^^^^^^^^

To solve this problem, Formz allows to **regroup templates for fields in standalone views**. The code required to integrate a field is then **much shorter and more understandable, but also more maintainable**: when a modification is done in a field layout, it is done for every real field which actually uses this layout.

-----

Usage
-----

Declaring a new layout
^^^^^^^^^^^^^^^^^^^^^^

To declare a new layout which can be used by the ViewHelper :ref:`integratorManual-viewHelpers-field`, you first have to **write its TypoScript configuration**, then **bind a template file** and finally create the **layout file**.

You are then able to use this new layout with any form field.

TypoScript Configuration
""""""""""""""""""""""""

The TypoScript configuration of your layout must be inside a **layout group**. Formz provides by default the group ``default``.

.. tip::

    In a project, it's advised to use a group name for this very project, for instance ``my-project``, or ``my-company``.

You can find all needed information in the chapter “:ref:`viewLayouts`”.

**Example:**

.. code-block:: typoscript

    config.tx_formz.view.layouts {
        my-project {
            templateFile = EXT:extension/Resources/Private/Templates/MyProject/Default.html
            items {
                one-column.layout = MyProject/OneColumn
                two-columns.layout = MyProject/TwoColumns
            }
        }
    }

Template file creation
""""""""""""""""""""""

The template file declared in the property ``templateFile`` of the TypoScript configuration must be created, if it doesn't exist yet. You should try to use this template with as much layouts as you can.

It's advised to divide your template in several sections, which contain the important blocks that are used by your layout. By default, three sections are used: ``Label``, ``Field`` and ``Feedback``.

The following variables can be used in your template:

* ``layout``: contains the path to the layout used, for instance ``MyProject/OneColumn``. You can use it with the ViewHelper ``f:layout``.

* ``formName``: name of the current form (the value of the property ``name`` used in the ViewHelper :ref:`integratorManual-viewHelpers-form`).

* ``fieldName``: name of the current field (the value of the property ``name`` used in the ViewHelper :ref:`integratorManual-viewHelpers-field`).

* ``fieldId``: if this argument was not declared before, it will be automatically filled with an identifier generated with the name of the form and the name of the field. Example for the field ``email`` of the form ``myForm``: ``formz-my-form-email``.

.. important::

    In order to be fully working with Formz, your template must respect all the rules defined in the chapter “:ref:`integratorManual-configuration`”.

**Example:**

*EXT:extension/Resources/Private/Templates/MyProject/Default.html*

.. code-block:: html

    <f:layout name="{layout}" />

    {namespace formz=Romm\Formz\ViewHelpers}

    <f:section name="Label">
        <label class="{f:if(condition: '{required}', then: 'required')}" for="{fieldId}">{label}</label>
    </f:section>

    <f:section name="Field">
        <div formz-field-container="{fieldName}">
            <formz:renderSlot slot="Field" />
        </div>
    </f:section>

    <f:section name="Feedback">
        <div formz-field-feedback-container="{fieldName}">
            <div formz-field-feedback-list="{fieldName}">
                <f:for each="{validationResults.errors}" iteration="iteration" as="error">
                    <formz:formatMessage message="{error}" />
                </f:for>
            </div>
        </div>
    </f:section>


Layout file creation
""""""""""""""""""""

The layout usage with the template is exactly the **same as in a classic Fluid integration**: you can render sections defined in the template.

Use the layout to get the wanted dividing, which may then be used by the form fields (see below).

**Example:**

*EXT:extension/Resources/Private/Layouts/MyProject/OneColumn.html*

.. code-block:: html

    <div class="row">
        <div class="col-md-4">
            <f:render section="Label" arguments="{_all}" />
        </div>
        <div class="col-md-4">
            <f:render section="Field" arguments="{_all}" />
        </div>
        <div class="col-md-4">
            <f:render section="Feedback" arguments="{_all}" />
        </div>
    </div>

Using a layout
^^^^^^^^^^^^^^

Once you registered your layouts, you can use them with the ViewHelper :ref:`integratorManual-viewHelpers-field` by filling the attribute ``layout``. The field will then use the wanted layout for its rendering, with chosen options.

This way, the field rendering stays completely outside your form integration.

If you need to know more, read the chapter “:ref:`integratorManual-viewHelpers-field`”.

**Example:**

.. code-block:: html
    :linenos:
    :emphasize-lines: 5

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:form action="submitForm" name="myForm">

        <formz:field name="email" layout="my-project.one-column">
            <formz:option name="required" value="1" />

            <formz:slot name="Field">
                <f:form.textfield property="{fieldName}" id="{fieldId}" placeholder="email" />
            </formz:slot>
        </formz:field>

    </formz:form>
