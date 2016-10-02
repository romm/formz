.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationBehaviours:

Comportements
=============

Un comportement est un processus lié à un champ, qui permettra de modifier dynamiquement la valeur de ce dernier. Il sera appelé avant la validation du champ, ce qui permet plus de souplesse dans les règles de gestion.

Un bon exemple est disponible dans le cœur de Formz : ``toLowerCase`` est un comportement permettant de transformer en minuscule la valeur d'un champ. Par exemple, cela peut être utilisé sur un champ contenant une adresse email.

.. hint::

    Par convention, dès qu'un nouveau comportement « générique » est créé, sa configuration devrait se trouver dans ``config.tx_formz.behaviours`` ; de cette manière, il pourra être réutilisé par différents champs.

Propriétés
----------

Retrouvez ci-dessous la liste des paramètres utilisables par un champ.

=========================================== =================
Propriété                                   Titre
=========================================== =================
\* :ref:`className <behaviourClassName>`    Nom de la classe
=========================================== =================

-----

.. _behaviourClassName:

Nom de la classe
----------------

.. container:: table-row

    Propriété
        ``className``
    Requis ?
        Oui
    Description
        Contient le nom de classe utilisée pour ce comportement.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.behaviours.toLowerCase {
                className = Romm\Formz\Behaviours\ToLowerCaseBehaviour
            }
