.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-dataAttributes:

Attributs « data »
==================

Un des principaux fonctionnements de Formz est la gestion d'attributs, automatiquement mis à jour dans la balise HTML ``<form>``.

Ces attributs vont permettre de savoir précisément quel est l'état de chacun des champs du formulaire : ce champ est-il valide ? Quelle est la valeur de ce champ ? Ce champ contient-t-il une erreur ?

Ainsi, il sera possible de mettre en place des sélecteurs CSS qui répondent au besoin.

**Exemple :**

.. code-block:: html

    <form name="exForm" class="formz" formz-value-has-animal="1"
          formz-valid-has-animal="1" formz-error-animal-name-required-default="1">
        ...
    </form>

Dans cet exemple, on trouve trois attributs :

* **formz-value-has-animal="1"**

  Contient la valeur du champ ``hasAnimal``, il s'agit vraisemblablement d'une case à cocher, et la valeur ``1`` signifie qu'elle est cochée.

* **formz-valid-has-animal="1"**

  Le champ ``hasAnimal`` a passé ses règles de validation avec succès.

* **formz-error-animal-name="1"**

  Le champ ``animalName`` contient au moins une erreur.

* **formz-error-animal-name-required-default="1"**

  Le champ ``animalName`` a actuellement une erreur, dont l'identifiant est ``required`` et la clé du message est ``default``.

Grâce à ces sélecteurs, il est possible de répondre à quasiment chaque situation possible dans le formulaire, et d'interragir avec.

Le cœur de Formz utilise notamment ces sélecteurs pour montrer ou cacher les conteneurs des champs et des messages.

-----

Liste des attributs « data »
----------------------------

Actuellement, Formz gère automatiquement les attributs suivants :

* ``formz-valid``

  Attribut rajouté lorsque **tous les champs** ont été testés et validés.

* ``formz-value-{field-name}``

  Où ``{field-name}`` est le nom du champ, au format minuscule et séparé par des tirets.

  Sera mis à jour avec la valeur actuelle du champ.

* ``formz-valid-{field-name}``

  Où ``{field-name}`` est le nom du champ, au format minuscule et séparé par des tirets.

  Sera rajouté lorsque le champ contiendra une valeur valide (les règles de validation qui lui sont associées ne renvoient pas d'erreur).

* ``formz-error-{field-name}``

  Où ``{field-name}`` est le nom du champ, au format minuscule et séparé par des tirets.

  Sera rajouté lorsque le champ contient au moins une erreur.

* ``formz-error-{field-name}-{validation-name}-{message-key}``

  Où ``{field-name}`` est le nom du champ, ``{validation-name}`` le nom de la règle de validation et ``{message-key}`` la clé du message renvoyé par la règle de validation, tous au format minuscule et séparé par des tirets.

  Sera rajouté lorsqu'une règle de validation renverra une erreur pour le champ.

* ``formz-loading``

  Attribut rajouté au conteneur d'un champ lorsqu'il est en train d'être validé. Il est notamment utile pour afficher un cercle de chargement lors d'une requête Ajax.

  Le même attribut est rajouté à la balise du formulaire lorsque le formulaire est en train d'être soumis.

* ``formz-submission-done``

  Lorsque le formulaire a été soumis, cet attribut est rajouté.

* ``formz-submitted``

  Lorsque le formulaire est en train d'être traité (la page est en train de charger), cet attribut est rajouté.
