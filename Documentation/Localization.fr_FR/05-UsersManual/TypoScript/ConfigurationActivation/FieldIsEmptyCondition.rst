.. include:: ../../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldIsEmpty:

« FieldIsEmpty »
================

Cette condition est vérifiée lorsqu'un champ n'a pas été rempli. Fonctionne avec les cases à cocher multiples.

Propriétés
----------

Ci-dessous un descriptif des paramètres utilisés par cette condition.

=============================================== =============
Propriété                                       Titre
=============================================== =============
\* :ref:`fieldName <fieldIsEmpty-fieldName>`    Nom du champ
=============================================== =============

-----

.. _fieldIsEmpty-fieldName:

Nom du champ
------------

.. container:: table-row

    Propriété
        ``fieldName``
    Requis ?
        Oui
    Description
        Le nom du champ qui n'est pas rempli.

-----

Exemple
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
