.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-class:

Class
=====

This ViewHelper handles dynamic classes defined in TypoScript (see chapter “:ref:`viewClasses`”).

It works like this: you use this ViewHelper **for a field of a form**, to initialize a **CSS class of a given category** (``valid`` or ``errors``). This class will be activated only when the field is in this category.

Read the example below to understand easily how it works.

.. note::

    The behaviour is handled both by PHP and JavaScript. You only have to use this ViewHelper, FormZ handles the rest.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Name of the class. Must be the combination of the class group (``valid`` or ``errors``) and the real name
                        of the class, separated by a dot. Example: ``valid.has-success``.

                        It must be a class defined in TypoScript (see chapter “:ref:`viewClasses`”).

``field``               If for any reason you are using this ViewHelper inside the ViewHelper :ref:`integratorManual-viewHelpers-field`,
                        you can fill the argument ``field`` with the name of the field which will be bound to this class.

                        It must be a valid field name for the current form.
======================= ================================================================================================================

Example
-------

We want the field ``email`` to have the class ``has-success`` if it passed all its validation rules.

First write the class registration in TypoScript:

.. code-block:: typoscript
    :linenos:
    :emphasize-lines: 2-4

    config.tx_formz.view.classes {
        valid {
            has-success = has-success
        }
    }

You may then use it in the template:

.. code-block:: html
    :linenos:
    :emphasize-lines: 7

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form action="submitForm" name="myForm">

        <fz:field name="email" layout="default">
            <f:form.textfield property="{fieldName}" id="{fieldId}"
                              class="{fz:class(name: 'valid.has-success')}"
                              placeholder="Email" />
        </fz:field>

    </fz:form>
