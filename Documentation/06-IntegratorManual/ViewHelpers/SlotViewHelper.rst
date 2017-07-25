.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-slot:

Slot
====

Defines a slot which can be rendered in the field layout with the ViewHelper “:ref:`integratorManual-viewHelpers-slot-render`”.

The goal of this ViewHelper is to dynamize some parts of the layout, for instance to be able to display informative messages for some fields.

.. important::

    This ViewHelper must be used inside a ViewHelper “:ref:`integratorManual-viewHelpers-field`”.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Name of the slot.

                        Note that if you use the name of a slot which is not used in the field layout, this slot wont be rendered.

``arguments``           Array of arbitrary arguments that will be passed to the slot and can be used within it as Fluid variables.
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 9-11

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form action="submitForm" name="myForm">

        <fz:field name="email" layout="default">

            <!-- This slot appears just before the list of validation messages. -->

            <fz:slot name="Messages.Out.Before" arguments="{myClass: 'info'}">
                <div class="{myClass}">Hello world!</div>
            </fz:slot>

        </fz:field>

    </fz:form>
