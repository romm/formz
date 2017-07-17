.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-slot:

Slot
====

Définit un slot dont le rendu pourra être utilisé dans le layout du champ avec le ViewHelper « :ref:`integratorManual-viewHelpers-slot-render` ».

Le but de ce ViewHelper est de pouvoir dynamiser certaines parties du layout, pour pouvoir par exemple afficher des messages informatifs pour certains champs.

.. important::

    Ce ViewHelper doit impérativement être utilisé à l'intérieur d'un ViewHelper « :ref:`integratorManual-viewHelpers-field` ».

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Nom du slot.

                        Notez que si vous utilisez le nom d'un slot qui n'est pas utilisé dans la layout du champ, ce slot ne sera donc
                        pas rendu.

``arguments``           Tableau d'arguments arbitraires qui seront passés au slot et où ils pourront être utilisés comme des variables
                        Fluid.
======================= ================================================================================================================

Exemple
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 10-12

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form action="submitForm" name="myForm">

        <fz:field name="email" layout="default">

            <!-- Ce slot apparaît juste avant l'affichage de la liste des messages
                 de validation. -->

            <fz:slot name="Messages.Out.Before" arguments="{myClass: 'info'}">
                <div class="{myClass}">Hello world!</div>
            </fz:slot>

        </fz:field>

    </fz:form>
