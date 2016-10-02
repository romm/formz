.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldHasValue:

« FieldHasValue »
=================

Cette condition est vérifiée lorsqu'un champ donné a la valeur donnée.

Propriétés
----------

Ci-dessous un descriptif des paramètres utilisés par cette condition.

=================================================== ================
Propriété                                           Titre
=================================================== ================
\* :ref:`fieldName <fieldHasValue-fieldName>`       Nom du champ

\* :ref:`fieldValue <fieldHasValue-fieldValue>`     Valeur du champ
=================================================== ================

-----

.. _fieldHasValue-fieldName:

Nom du champ
------------

.. container:: table-row

    Propriété
        ``fieldName``
    Requis ?
        Oui
    Description
        Le nom du champ désiré.

.. _fieldHasValue-fieldValue:

Valeur du champ
---------------

.. container:: table-row

    Propriété
        ``fieldValue``
    Requis ?
        Oui
    Description
        Le valeur que le champ doit avoir pour que la condition soit vérifiée.

Exemple
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
