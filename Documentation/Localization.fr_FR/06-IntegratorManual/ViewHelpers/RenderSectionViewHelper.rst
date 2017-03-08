.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-slot-render:

Slot.Render
===========

Lance le rendu d'un slot défini à l'intérieur du ViewHelper « :ref:`integratorManual-viewHelpers-field` » avec le ViewHelper « :ref:`integratorManual-viewHelpers-slot` ».

Ce ViewHelper est utile lorsqu'il est utilisé dans un layout de champ (cf. le chapitre « :ref:`integratorManual-layouts` »).

.. important::

    Ce ViewHelper doit impérativement être utilisé à l'intérieur d'un ViewHelper « :ref:`integratorManual-viewHelpers-field` ».

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``slot``             Nom du slot qui sera rendu.
======================= ================================================================================================================

Exemple
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 3,7

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:slot.render slot="Label.Before" />

    <label for="{fieldId}">{label}</label>

    <formz:slot.render slot="Label.After" />
