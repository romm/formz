.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationActivation:

Activation
==========

.. note::

    You can find an explanation on how field activation works in the chapter “:ref:`fieldsActivation`”.

Properties
----------

You can find below a description of the parameters for an activation condition.

=============================== =========================
Property                        Title
=============================== =========================
\* :ref:`type <conditionType>`  Condition type

\…                              Condition options
=============================== =========================

-----

.. _conditionType:

Condition type
--------------

.. container:: table-row

    Property
        ``activation``
    Required?
        No
    Description
        Contains the condition type. You can find all the condition types available below.

        **Example:**

        .. code-block:: typoscript

            activation {
                items {
                    emailIsValid {
                        type = fieldIsValid
                        fieldName = email
                    }
                }
            }

Activation condition list
-------------------------

You can find below the list of all activation condition available in Formz core.

They can be used by fields (see “:ref:`Activation conditions <fieldsActivation-items>`”) and validators (see “:ref:`Validator activation <validatorsActivation>`”).

.. toctree::
    :maxdepth: 5
    :titlesonly:

    FieldHasValueCondition
    FieldIsEmptyCondition
    FieldIsValidCondition
    FieldHasErrorCondition

