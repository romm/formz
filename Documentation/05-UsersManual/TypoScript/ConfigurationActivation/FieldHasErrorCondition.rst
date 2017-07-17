.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldHasError:

« FieldHasError »
=================

This condition is verified when a given field has a specific error in a specific validation rule.

Properties
----------

You can find below a list of parameters usable by this condition.

=========================================================== ======================
Propriété                                                   Titre
=========================================================== ======================
\* :ref:`fieldName <fieldHasError-fieldName>`               Name of the field

\* :ref:`validationName <fieldHasError-validationName>`     Validation rule

:ref:`errorName <fieldHasError-errorName>`                  Name of the error
=========================================================== ======================

-----

.. _fieldHasError-fieldName:

Name of the field
-----------------

.. container:: table-row

    Property
        ``fieldName``
    Required?
        Yes
    Description
        The name of the field which has the error.

.. _fieldHasError-validationName:

Name of the validation rule
---------------------------

.. container:: table-row

    Property
        ``fieldName``
    Required?
        Yes
    Description
        The name of the validation rule which returns the error. For instance ``required``.

.. _fieldHasError-errorName:

Name of the error
-----------------

.. container:: table-row

    Property
        ``errorName``
    Required?
        Yes
    Description
        The name of the returned error. Most of the time it will be the default value: ``default``.

        Note that if the value is not filled, it will automatically be set to ``default``.

Example
-------

.. code:: typoscript

    activation {
        items {
            emailHasErrorRequired {
                type = fieldHasError
                fieldName = email
                validationName = required
            }
        }
    }
