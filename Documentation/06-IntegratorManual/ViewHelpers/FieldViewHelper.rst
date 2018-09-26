.. include:: ../../Includes.txt

.. _integratorManual-viewHelpers-field:

Field
=====

Allows to configure the rendering of a field of the form.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Name of the field.

                        It must be an accessible property of the :ref:`form model <developerManual-php-model>`.

\* ``layout``           Layout used for this field. For more information, read the chapter “:ref:`integratorManual-layouts`”.

``arguments``           List of arguments sent to the rendering of the field layout. If you can, you should prefer using the ViewHelper
                        :ref:`integratorManual-viewHelpers-option` instead, which allows a better code readability.
======================= ================================================================================================================

Example
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 5,7

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form action="submitForm" name="myForm">

        <fz:field name="email" layout="default">
            ...
        </fz:field>

    </fz:form>
