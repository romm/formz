.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-slot-has:

Slot.Has
========

Adds a condition that is verified if a slot has been defined with the ViewHelper “:ref:`integratorManual-viewHelpers-slot`”.

This ViewHelper is useful when used inside a field layout (see chapter “:ref:`integratorManual-layouts`”).

.. important::

    This ViewHelper must be used inside a ViewHelper “:ref:`integratorManual-viewHelpers-field`”.

.. note::

    This ViewHelper can be used the same way ``<f:if>`` is used; it means ``<f:then>`` and ``<f:else>`` will work normally.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``slot``             Name of the slot that will determine the rendering of the block.
======================= ================================================================================================================

Examples
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
