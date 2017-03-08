.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers:

ViewHelpers
===========

Formz provides a set of ViewHelpers to help integration:

- :ref:`formz:form <integratorManual-viewHelpers-form>`

  Will initialize Formz. It replaces the ViewHelper ``form`` provided by Extbase, and must absolutely be used.

- :ref:`formz:field <integratorManual-viewHelpers-field>`

  Renders a field of the form, by automatizing many processes.

- :ref:`formz:option <integratorManual-viewHelpers-option>`

  Defines the value of an option, which can be used later in the field rendering.

- :ref:`formz:slot <integratorManual-viewHelpers-slot>`

  Defines a slot in the template of a field.

- :ref:`formz:slot.render <integratorManual-viewHelpers-slot-render>`

  Launches the rendering of a slot defined in the template of a field.

- :ref:`formz:formatMessage <integratorManual-viewHelpers-formatMessage>`

  Will format a message returned by the validation of a field.

- :ref:`formz:class <integratorManual-viewHelpers-class>`

  Handles dynamically the CSS classes depending on the validity of a field: activates or deactivates CSS classes according to the result of the field validation.

.. toctree::
    :maxdepth: 5
    :titlesonly:
    :hidden:

    FormViewHelper
    FieldViewHelper
    OptionViewHelper
    SlotViewHelper
    Slot\RenderViewHelper
    FormatMessageViewHelper
    ClassViewHelper
