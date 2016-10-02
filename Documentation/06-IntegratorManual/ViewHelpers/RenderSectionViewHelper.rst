.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-renderSection:

RenderSection
=============

Renders a section defined in the ViewHelper “:ref:`integratorManual-viewHelpers-field`” with the ViewHelper “:ref:`integratorManual-viewHelpers-section`”.

This ViewHelper is useful when used inside a field layout (see chapter “:ref:`integratorManual-layouts`”).

.. important::

    This ViewHelper must be used inside a ViewHelper “:ref:`integratorManual-viewHelpers-field`”.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``section``          Name of the rendered section.
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 3,7

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:renderSection section="Label.Before" />

    <label for="{fieldId}">{label}</label>

    <formz:renderSection section="Label.After" />
