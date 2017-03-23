.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _usersManual-typoScript-configurationBehaviours:

Behaviours
==========

A behaviour is a process bound to a field, and allows to dynamically modify its value. It will be called before the validation of a field, allowing more flexibility to the rules.

A good example is available within the core of FormZ: ``toLowerCase`` is a behaviour to transform to lower case the value of a field. For instance, it can be used on a field containing an email address.

.. hint::

    By convention, every time a new common behaviour is configured, its configuration should be set at the path ``config.tx_formz.behaviours``; this way, it may be used again by different fields.

Properties
----------

You can find below the list of parameters usable by a field.

=========================================== =================
Property                                    Title
=========================================== =================
\* :ref:`className <behaviourClassName>`    Name of the class
=========================================== =================

-----

.. _behaviourClassName:

Name of the class
-----------------

.. container:: table-row

    Property
        ``className``
    Required?
        Yes
    Description
        Contains the name of the PHP class used by this behaviour.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.behaviours.toLowerCase {
                className = Romm\Formz\Behaviours\ToLowerCaseBehaviour
            }
