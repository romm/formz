.. include:: ../../../../Includes.txt

.. _integratorManual-viewHelpers-slot-has:

Slot.Has
========

Conditionne le rendu d'un bloc sur la présence d'un slot, défini grâce au ViewHelper « :ref:`integratorManual-viewHelpers-slot` ».

Ce ViewHelper est utile lorsqu'il est utilisé dans un layout de champ (cf. le chapitre « :ref:`integratorManual-layouts` »).

.. important::

    Ce ViewHelper doit impérativement être utilisé à l'intérieur d'un ViewHelper « :ref:`integratorManual-viewHelpers-field` ».

.. note::

    Il est possible d'utiliser ce ViewHelper de la même manière que le ViewHelper ``<f:if>`` est utilisé ; cela signifie que ``<f:then>`` et ``<f:else>`` fonctionneront normalement.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``slot``             Nom du slot qui conditionnera le rendu.
======================= ================================================================================================================

Exemples
--------

.. code-block:: html
    :linenos:
    :emphasize-lines: 4,8

    {namespace fz=Romm\Formz\ViewHelpers}

    <div class="container">
        <fz:slot.has slot="Image">
            <div class="image">
                <fz:slot.render slot="Image" />
            </div>
        </fz:slot.has>
    </div>

.. code-block:: html
    :linenos:
    :emphasize-lines: 4,5,7,8,10,11

    {namespace fz=Romm\Formz\ViewHelpers}

    <div class="container">
        <fz:slot.has slot="Image">
            <f:then>
                <fz:slot.render slot="Image" />
            </f:then>
            <f:else>
                <img src="default-image.jpg" />
            </f:else>
        </fz:slot.has>
    </div>
