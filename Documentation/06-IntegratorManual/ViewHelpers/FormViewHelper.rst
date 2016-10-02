.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-form:

Form
====

Replaces the basic form ViewHelper from Fluid (``<f:form>``).

It works the same way as the basic ViewHelper, expect that the argument ``name`` is required (see below).

.. important::

    You **must use** this ViewHelper when integrating a form using Formz.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Must be exactly the name of the variable in the controller action on which the form is bound (see example
                        below).

\…                      You can use all available arguments from the basic Fluid ViewHelper.
======================= ================================================================================================================

Example
-------

.. code-block:: html

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:form action="submitForm" name="myForm">
        ...
    </formz:form>

The form below is bound to the action ``submitForm``. If we check the action in the controller PHP class:

.. code-block:: php

    public function submitFormAction(MyForm $myForm)
    {
        // ...
    }

The name of the variable is ``$myForm``: the parameter ``name`` of the ViewHelper must be the same.

For the rest, nothing changes, you can use all features of a basic Fluid form.
