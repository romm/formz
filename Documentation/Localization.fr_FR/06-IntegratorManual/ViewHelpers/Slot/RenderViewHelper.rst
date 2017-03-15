.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../../Includes.txt

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

``arguments``           Tableau d'arguments arbitraires qui seront passés au slot et où ils pourront être utilisés comme des variables
                        Fluid.

                        .. note::

                            Si un argument défini dans cette liste est également rempli dans les ``arguments`` du slot (cf.
                            « :ref:`integratorManual-viewHelpers-slot` », il sera écrasé par la valeur indiquée dans la définition du
                            slot.
======================= ================================================================================================================

Exemple
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 3,7

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:slot.render slot="Label.Before" arguments="{class: 'label-before'}" />

    <label for="{fieldId}">{label}</label>

    <fz:slot.render slot="Label.After" arguments="{class: 'label-after'}" />
