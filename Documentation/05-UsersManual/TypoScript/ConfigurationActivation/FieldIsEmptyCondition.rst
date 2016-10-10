.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldIsEmpty:

« FieldIsEmpty»
================

This condition is verified when a given field was not filled. Works witch multiple checkboxes.

Properties
----------

You can find below a list of parameters usable by this condition.

=============================================== ===================
Property                                        Title
=============================================== ===================
\* :ref:`fieldName <fieldIsEmpty-fieldName>`    Name of the field
=============================================== ===================

-----

.. _fieldIsEmpty-fieldName:

Name of the field
-----------------

.. container:: table-row

    Property
        ``fieldName``
    Required?
        Yes
    Description
        The name of the field which is empty.

-----

Example
-------

.. code:: typoscript

    activation {
        items {
            emailIsEmpty {
                type = fieldIsEmpty
                fieldName = email
            }
        }
    }
