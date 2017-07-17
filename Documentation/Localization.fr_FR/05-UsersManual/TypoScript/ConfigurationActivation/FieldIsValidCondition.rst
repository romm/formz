.. include:: ../../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldIsValid:

« FieldIsValid »
================

Cette condition est vérifiée lorsqu'un champ donné a passé ses règles de validation avec succès.

Propriétés
----------

Ci-dessous un descriptif des paramètres utilisés par cette condition.

=============================================== =============
Propriété                                       Titre
=============================================== =============
\* :ref:`fieldName <fieldIsValid-fieldName>`    Nom du champ
=============================================== =============

-----

.. _fieldIsValid-fieldName:

Nom du champ
------------

.. container:: table-row

    Propriété
        ``fieldName``
    Requis ?
        Oui
    Description
        Le nom du champ qui doit être valide.

-----

Exemple
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
