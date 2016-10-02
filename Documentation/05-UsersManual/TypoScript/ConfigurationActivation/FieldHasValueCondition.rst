.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldHasValue:

« FieldHasValue »
=================

This condition is verified when a given field has a given value.

Properties
----------

You can find below a list of parameters usable by this condition.

=================================================== ===================
Property                                            Title
=================================================== ===================
\* :ref:`fieldName <fieldHasValue-fieldName>`       Name of the field

\* :ref:`fieldValue <fieldHasValue-fieldValue>`     Value of the field
=================================================== ===================

-----

.. _fieldHasValue-fieldName:

Name of the field
-----------------

.. container:: table-row

    Property
        ``fieldName``
    Required?
        Yes
    Description
        Name of the wanted field.

.. _fieldHasValue-fieldValue:

Value of the field
------------------

.. container:: table-row

    Property
        ``fieldValue``
    Required?
        Yes
    Description
        The value which the field must have in order for the condition to be verified.

Example
-------

.. code:: typoscript

    activation {
        items {
            colorIsRed {
                type = fieldHasValue
                fieldName = color
                fieldValue = red
            }
        }
    }
