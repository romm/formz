.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-slot-render:

Slot.Render
===========

Renders a slot defined in the ViewHelper “:ref:`integratorManual-viewHelpers-field`” with the ViewHelper “:ref:`integratorManual-viewHelpers-slot`”.

This ViewHelper is useful when used inside a field layout (see chapter “:ref:`integratorManual-layouts`”).

.. important::

    This ViewHelper must be used inside a ViewHelper “:ref:`integratorManual-viewHelpers-field`”.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``slot``             Name of the rendered slot.
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 3,7

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:slot.render slot="Label.Before" />

    <label for="{fieldId}">{label}</label>

    <fz:slot.render slot="Label.After" />
