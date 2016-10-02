.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldIsValid:

« FieldIsValid »
================

This condition is verified when a given field did pass its validation successfully.

Properties
----------

You can find below a list of parameters usable by this condition.

=============================================== ===================
Property                                        Title
=============================================== ===================
\* :ref:`fieldName <fieldIsValid-fieldName>`    Name of the field
=============================================== ===================

-----

.. _fieldIsValid-fieldName:

Name of the field
-----------------

.. container:: table-row

    Property
        ``fieldName``
    Required?
        Yes
    Description
        The name of the field which must be valid.

-----

Example
-------

.. code:: typoscript

    activation {
        items {
            emailIsValid {
                type = fieldIsValid
                fieldName = email
            }
        }
    }
