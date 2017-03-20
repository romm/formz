.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _integratorManual-configuration:

Configuration
=============

In order for CSS and JavaScript to be able to find elements in the HTML DOM, it's necessary to respect some integration rules.

Take care of respecting them when :ref:`you create new layouts <integratorManual-layouts>`, or FormZ may not work properly.

Field container
---------------

A field must always have a **container**, which is used by FormZ to **display or hide the entire block** under some conditions.

.. code-block:: html
    :linenos:
    :emphasize-lines: 1

    <div fz-field-container="email">
        <label for="email">Email:</label>

        <f:form.textfield property="email" id="email" />
    </div>

In this example, the first element ``<div>`` is considered by FormZ as the container of the field ``email``.

By default, the attribute which you should be using is ``fz-field-container``, which must contain the name of the field. It is possible to customize the used attribute, see “:ref:`Field container selector<fieldsSettings-fieldContainerSelector>`”.

-----

Message container
-----------------

The same way the field container must be registered, the message container for this field must also be defined. Then, FormZ can display or hide this element according to the presence of messages (mainly errors) or not.

This container is divided into two parts: a global container, and a container for the list of messages. For instance:

.. code-block:: html
    :linenos:
    :emphasize-lines: 1,4

        <div fz-field-message-container="email">
            This field contains at least an error:

            <div fz-field-message-list="email">
                <f:form.validationResults for="exForm.email">
                    <f:for each="{validationResults.errors}" as="error">
                        <fz:formatMessage message="{error}" />
                    </f:for>
                </f:form.validationResults>
            </div>
        </div>

In this example, the messages container is identified with ``fz-field-message-container="email"``, and the container of the messages list with ``fz-field-message-list="email"``. If the field ``email`` does not contain any message, the first container will be hidden.

The attributes used in this example are the default ones. It's possible to customize them, see “:ref:`Messages container selector <fieldsSettings-messageContainerSelector>`” and “:ref:`Messages list container selector <fieldsSettings-messageListSelector>`”.

-----

.. _integratorManual-configuration-messageTemplate:

Message template
----------------

Messages are automatically handled by JavaScript and Fluid. It works this way: when a field returns a message, it's wrapped inside a HTML template, then put into the messages container (see above).

Of course, it can be necessary that the message template changes according to the platforms, style guides, and other things. You can customize the template of messages for every field. The default value of this template is:

.. code-block:: html

    <span class="js-validation-rule-#VALIDATOR# js-validation-type-#TYPE# js-validation-message-#KEY#">#MESSAGE#</span>

In the template, the following values are dynamically replaced:

* **#FIELD#**: name of the field;

* **#FIELD_ID#**: “id” attribute of the field. Note that for fields of type “radio” or “checkbox” using this marker is useless.

* **#VALIDATOR#**: name of the validation rule which returned this message. For instance, it can be ``required``;

* **#TYPE#**: type of the message, usually an error (in which case the value is ``error``);

* **#KEY#**: key of the message. In most cases, it's set to ``default``;

* **#MESSAGE#**: body of the message.

You can customize the message template in several ways:

TypoScript Configuration
^^^^^^^^^^^^^^^^^^^^^^^^

You can configure with TypoScript the value of the template, in the fields configuration: “:ref:`Message template <fieldsSettings-messageTemplate>`”.

Note that you can also modify the default value for all fields: “:ref:`Default fields configuration <settingsDefaultFieldSettings>`”.

HTML block
^^^^^^^^^^

You can insert, directly in your template, a HTML block that contains the template. The block container must have the attribute ``fz-message-template="1"``.

By convention, this container should be a tag ``<script>`` of type ``text/template``.

**Example:**

.. code-block:: html

    <script type="text/template" fz-message-template="1">
        <li class="#TYPE#">#MESSAGE#</li>
    </script>

If JavaScript spots this block, it will use its content as a template for messages.

You can insert this block in two places:

1. **Inside a field container**

   The template will be used ONLY for this field.

2. **Inside the body of the tag** ``<form>``

   In this case, this template will be used by default for all the form fields.

   Note that a template located inside a field container will be taken over the one inside the tag ``<form>``.
