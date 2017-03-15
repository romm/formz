.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-option:

Option
======

Defines the value of an argument that is sent during the rendering of a field.

It's another way of using ``arguments`` of the ViewHelper “:ref:`integratorManual-viewHelpers-field`”. You can use both features, but the ViewHelper ``option`` will override an argument previously defined.

.. important::

    This ViewHelper must be used inside a ViewHelper “:ref:`integratorManual-viewHelpers-field`”.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Name of the argument.

\* ``value``            Value of the argument (may be of any type).
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 6

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form action="submitForm" name="myForm">

        <fz:field name="email" layout="default">
            <fz:option name="required" value="1">

            ...
        </fz:field>

    </fz:form>
