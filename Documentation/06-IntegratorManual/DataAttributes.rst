.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _integratorManual-dataAttributes:

“data” attributes
=================

One of the main features of Formz is the handling of attributes, automatically updated in the HTML tag ``<form>``.

These attributes allow to know exactly what is the state of every field in the form: is this field valid? What is the value of this field? Does this field contain an error?

This way, you can set up CSS selectors that follow your needs.

**Example:**

.. code-block:: html

    <form name="exForm" class="formz" formz-value-has-animal="1"
          formz-valid-has-animal="1" formz-error-animal-name-required-default="1">
        ...
    </form>

In this example, there are three attributes:

* **formz-value-has-animal="1"**

  Contains the value of the field ``hasAnimal``, it must be a checkbox, and the value ``1`` means it is checked.

* **formz-valid-has-animal="1"**

  The field ``hasAnimal`` did pass its validation rules.

* **formz-error-animal-name="1"**

  The field ``animalName`` contains at least one error.

* **formz-error-animal-name-required-default="1"**

  The field ``animalName`` contains an error, whose identifier is ``required`` and the message key is ``default``.

Thanks to these selectors, it's possible to answer almost every situation in the form, and interact with it.

The core of Formz uses these selectors to show or hide the containers of the fields and their messages.

-----

“data” attributes list
----------------------

Currently, Formz automatically handle the following attributes:

* ``formz-valid``

  Attribute added when **all fields** have been tested and validated.

* ``formz-value-{field-name}``

  Where ``{field-name}`` is the name of the field, in dashed lower case.

  It will be updated when the current value of the field.

* ``formz-valid-{field-name}``

  Where ``{field-name}`` is the name of the field, in dashed lower case.

  Will be added when the field contains a valid value (its validation rules did not return any error).

* ``formz-error-{field-name}``

  Where ``{field-name}`` is the name of the field, in dashed lower case.

  Will be added when the field contains at least one error.

* ``formz-error-{field-name}-{validation-name}-{message-key}``

  Where ``{field-name}`` is the name of the field, ``{validation-name}`` the name of the validation rule and ``{message-key}`` the key of the message returned by the validation rule, all in dashed lower case.

  Will be added when a validation rule returns an error for the field.

* ``formz-loading``

  Attribute added to the container of a field when it's being validated. It is mainly useful to display a loading circle during an Ajax request.

  The same attribute is added to the form tag when the form is being submitted.

* ``formz-submission-done``

  When the form has been submitted, this attribute is added.

* ``formz-submitted``

  When the form is being processed (the page is loading), this attribute is added.
