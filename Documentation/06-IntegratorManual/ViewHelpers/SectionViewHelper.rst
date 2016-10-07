.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-section:

Section
=======

Defines a section which can be rendered in the field layout with the ViewHelper “:ref:`integratorManual-viewHelpers-renderSection`”.

The goal of this ViewHelper is to dynamize some parts of the layout, for instance to be able to display informative messages for some fields.

.. important::

    This ViewHelper must be used inside a ViewHelper “:ref:`integratorManual-viewHelpers-field`”.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Name of the section.

                        Note that if you use the name of a section which is not used in the field layout, this section wont be rendered.
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 9-11

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:form action="submitForm" name="myForm">

        <formz:field name="email" layout="default">

            This section appears just before the list of validation messages.
            <formz:section name="Feedback.Out.Before">
                <div class="info">Hello world!</div>
            </formz:section>

        </formz:field>

    </formz:form>
