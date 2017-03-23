.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../../Includes.txt

.. _usersManual-typoScript-configurationActivation:

Activation
==========

.. note::

    Vous pouvez retrouver une explication sur le fonctionnement des activations de champs au chapitre « :ref:`fieldsActivation` ».

Propriétés
----------

Ci-dessous un descriptif des paramètres pour une condition d'activation.

=============================== =========================
Propriété                       Titre
=============================== =========================
\* :ref:`type <conditionType>`  Type de condition

\…                              Options de la condition
=============================== =========================

-----

.. _conditionType:

Type de condition
-----------------

.. container:: table-row

    Propriété
        ``activation``
    Requis ?
        Non
    Description
        Contient le type de condition. Retrouvez tous les types de conditions disponibles plus bas.

        **Exemple :**

        .. code-block:: typoscript

            activation {
                items {
                    emailIsValid {
                        type = fieldIsValid
                        fieldName = email
                    }
                }
            }

Liste des conditions d'activation
---------------------------------

Retrouvez ci-dessous la liste des conditions d'activation disponibles dans le cœur de FormZ.

Elles peuvent être utilisées par les champs (cf. « :ref:`Conditions d'activation <fieldsActivation-conditions>` ») et les validateurs (cf « :ref:`Activation du validateur <validatorsActivation>` »).

.. toctree::
    :maxdepth: 5
    :titlesonly:

    FieldHasValueCondition
    FieldIsEmptyCondition
    FieldIsValidCondition
    FieldHasErrorCondition

