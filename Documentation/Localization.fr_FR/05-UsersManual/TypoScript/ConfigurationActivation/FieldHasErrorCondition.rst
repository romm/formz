.. include:: ../../../../Includes.txt

.. _usersManual-typoScript-configurationActivation-fieldHasError:

« FieldHasError »
=================

Cette condition est vérifiée lorsqu'un champ donné a rencontré une erreur spécifique lors d'une règle de validation spécifique.

Propriétés
----------

Ci-dessous un descriptif des paramètres utilisés par cette condition.

=========================================================== ======================
Propriété                                                   Titre
=========================================================== ======================
\* :ref:`fieldName <fieldHasError-fieldName>`               Nom du champ

\* :ref:`validationName <fieldHasError-validationName>`     Règle de validation

:ref:`errorName <fieldHasError-errorName>`                  Nom de l'erreur
=========================================================== ======================

-----

.. _fieldHasError-fieldName:

Nom du champ
------------

.. container:: table-row

    Propriété
        ``fieldName``
    Requis ?
        Oui
    Description
        Le nom du champ qui rencontre l'erreur.

.. _fieldHasError-validationName:

Nom de la règle de validation
-----------------------------

.. container:: table-row

    Propriété
        ``fieldName``
    Requis ?
        Oui
    Description
        Le nom de la règle de validation qui renverra une erreur. Exemple : ``required``.

.. _fieldHasError-errorName:

Nom de l'erreur
---------------

.. container:: table-row

    Propriété
        ``errorName``
    Requis ?
        Oui
    Description
        Le nom de l'erreur renvoyée. La plupart du temps il s'agira de la valeur par défaut : ``default``.

        Notez que si la valeur n'est pas renseignée, elle sera automatiquement mise à ``default``.

Exemple
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
