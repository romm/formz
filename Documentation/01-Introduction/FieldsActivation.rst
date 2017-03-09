.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _fieldsActivation:

Fields activation
=================

FormZ provides an easy way of handling fields activation under certain conditions.

Principle
---------

Activation can be applied directly **on a form's fields**, or on a specific **field validation rule**. The goal is to activate the process only when a given boolean expression is verified.

**Examples:**

1. A form has a radio button “Do you have a pet?”.

   If the user checks “Yes”, then we want to know the name of the animal: a second text field “Name of the animal” is activated, it appears and its validation becomes active.

2. A user can fill its first and last name, but these fields are optional.

   If one of the two fields is filled, then we want to know the other one as well. We must then activate the validation rule “required” of a field only when the other one is filled.

In the previous cases, a field activation means several things:

1. The field will be **displayed or hidden** depending on if it is activated or not (using CSS).
2. In JavaScript, the field validation rules will only run when the field is activated.
3. Same on the server side, the field validation will only run when the field is activated.

This field activation system presents a major advantage: FormZ will be able to automatize the wanted behaviours, with PHP but also with JavaScript and CSS, by automatically generating code, which will be directly injected in the page and automatize the behaviours listed above.

It prevents the obligation to write CSS/JavaScript/PHP code for each field activation case, but also means that activation rules are **gathered in a single place** (TypoScript) and not dispersed in CSS/JavaScript/PHP files.

-----

How does it work?
-----------------

The activation is configured thanks to two properties: **activation conditions**, and a **boolean expression**.

Conditions
^^^^^^^^^^

Conditions can be configured in two places: either at the root of a form configuration, or at the root of a field configuration.

Potentially, any condition can be verified, as long as a proper PHP implementation exists. FormZ already provides several basic conditions, like “:ref:`fieldHasValue <usersManual-typoScript-configurationActivation-fieldHasValue>`” or “:ref:`fieldIsValid <usersManual-typoScript-configurationActivation-fieldIsValid>`”. It is possible to create new conditions to fit specific needs.

You can find the different existing conditions and their configuration at the chapter “:ref:`usersManual-typoScript-configurationActivation`”.

The expression
^^^^^^^^^^^^^^

The boolean expression allows to use several condition thanks to logical operators: the logical “and”, the logical “or”. It also allows to gather expressions thanks to parenthesis.

**Example:**

``(colorIsRed || colorIsBlue) && emailIsValid``

This expression is valid when ``the selected color is red and the email is valid``, **or** when ``the selected color is blue and the email is valid``.
