.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _usage:

Installation et utilisation
===========================

Installation
^^^^^^^^^^^^

Vous retrouverez ci-dessous un schéma permettant de comprendre facilement le déroulement de l'utilisation d'un formulaire :

.. only:: html

    .. figure:: ../../Images/schema-form-creation.svg
        :alt: Schéma de création de formulaire
        :figwidth: 70%

-----

Téléchargement
--------------

Pour que Formz fonctionne correctement, vous devrez installer :

- **configuration_object** – cette extension permet de convertir la configuration TypoScript de Formz. Sans rentrer dans les détails, elle permet notamment de détecter les erreurs dans la configuration. Elle est obligatoire.

  Vous pouvez l'installer :

  - Via Composer : ``composer require romm/configuration-object:*``
  - Via le TER : `Configuration Object <https://typo3.org/extensions/repository/view/configuration_object>`_

- **formz** – le cœur de l'extension.

  Vous pouvez l'installer :

  - Via Composer : ``composer require romm/formz:*``
  - Via le TER : `Formz <https://typo3.org/extensions/repository/view/formz>`_

-----

Création d'un nouveau formulaire
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Retrouvez ci-dessous les principales étapes de mise en place d'un nouveau formulaire. Notez que vous pouvez consulter le mini-guide résumant la création d'un formulaire complet ici : « :ref:`tutorial` ».

.. tip::

    Pour comprendre rapidement le fonctionnement, vous pouvez télécharger une extension contenant un modèle de formulaire dans le chapitre « :ref:`example` ».

Développement
-------------

Afin d'entammer la création d'un nouveau formulaire, il est conseillé de commencer par le développement de l'architecture PHP.

Un formulaire sera représenté par **un modèle de données** (cf. « :ref:`developerManual-php-model` »), et son affichage devra passer par un plug-in (ou autre), géré par un **contrôleur**. Jusque là, cela reste très classique et proche d'une extension basée sur Extbase.

La réelle utilité de l'extension viendra avec l'utilisation des **validateurs de formulaires** et **validateurs de champs**. Consultez les chapitres « :ref:`developerManual-php-formValidator` » et « :ref:`developerManual-php-validator` ».

-----

Intégration
-----------

L'intégration HTML d'un formulaire est similaire à une intégration Fluid classique, mais quelques outils devront être utilisés, et quelques normes respectées pour assurer un bon fonctionnement du formulaire.

Consultez les chapitres de « :ref:`integratorManual` » pour en savoir plus.

-----

Configuration
-------------

Une fois que l'affichage sera géré, les règles de validations pourront être gérées via de la configuration TypoScript.

Pour apprendre comment configurer les différents paramétrages, consultez les chapitres de « :ref:`usersManual` ».

-----

Retrouvez ci-dessous un exemple d'arborescence des fichiers nécessaires à la mise en place d'un formulaire :

.. only:: html

    .. figure:: ../../Images/files-tree.svg
        :alt: Liste des fichiers pour un formulaire
        :figwidth: 300px

-----

Mode « Debug »
^^^^^^^^^^^^^^

Un mode « Debug » est disponible et permet, si activé, d'avoir des informations supplémentaires en cas de problème.

Pour l'activer, allez dans le gestionnaire d'extension, rentrez dans les options de Formz, et cochez la case « **debugMode** ».

.. warning::

    Il est **fortement déconseillé** d'activer ce mode dans un **environnement de production** !
