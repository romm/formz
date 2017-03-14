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

``arguments``           Array of arbitrary arguments that will be passed to the slot and can be used within it as Fluid variables.

                        .. note::

                            If an argument defined in this list is also filled in the ``arguments`` of the slot (see
                            “:ref:`integratorManual-viewHelpers-slot`”), it will be overridden with the value defined in the slot
                            definition.
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 3,7

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:slot.render slot="Label.Before" arguments="{class: 'label-before'}" />

    <label for="{fieldId}">{label}</label>

    <formz:slot.render slot="Label.After" arguments="{class: 'label-after'}" />
