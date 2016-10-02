.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-renderSection:

RenderSection
=============

Lance le rendu d'une section définie à l'intérieur du ViewHelper « :ref:`integratorManual-viewHelpers-field` » avec le ViewHelper « :ref:`integratorManual-viewHelpers-section` ».

Ce ViewHelper est utile lorsqu'il est utilisé dans un layout de champ (cf. le chapitre « :ref:`integratorManual-layouts` »).

.. important::

    Ce ViewHelper doit impérativement être utilisé à l'intérieur d'un ViewHelper « :ref:`integratorManual-viewHelpers-field` ».

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``section``          Nom de la section qui sera rendue.
======================= ================================================================================================================

Exemple
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 3,7

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:renderSection section="Label.Before" />

    <label for="{fieldId}">{label}</label>

    <formz:renderSection section="Label.After" />
