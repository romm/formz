.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

Présentation
============

FormZ
^^^^^

*Retrouvez le site officiel de FormZ sur :* `typo3-formz.com <http://typo3-formz.com/>`_

Les formulaires sont des éléments **prédominants dans la conception d'un site internet** puisqu'ils permettent l'**interaction directe** entre l'utilisateur et l'application. Techniquement, la mise en place d'un formulaire peut rapidement devenir **complexe** et demander **beaucoup de temps** : de nombreux aspects sont à prendre en compte : **style, affichage, validation, sécurité**…

C'est de ce constat que FormZ est né : faciliter la **mise en place** et la **maintenance** d'un formulaire, en proposant des outils **simples et rapides d'utilisation**, pour autant **puissants** et **flexibles** pour répondre à tous les besoins.

FormZ facilite les sujets suivants :

- **HTML** – des outils sont mis à disposition pour Fluid, afin de faciliter l'intégration.
- **Validation** – avec une configuration basée sur TypoScript, toutes les règles de validation des champs sont extrêmement simples à mettre en place et maintenir.
- **Style** – avec son système « d'attributs data » poussé, FormZ peut répondre à quasiment tous les besoins en terme d'affichage.
- **UX** – toute une API JavaScript est mise à disposition pour rendre l'expérience utilisateur aussi rapide et plaisante que possible.
- **Génération de code** – FormZ se charge de générer des blocs de code JavaScript et CSS, qui seront injectés dans la page et automatiseront une partie des comportements côté client.

À quoi ça sert ?
----------------

Comme indiqué précédemment, FormZ a pour but d'accélérer les développements de formulaires, allant par exemple du simple formulaire de contact au formulaire complexe de souscription. L'extension propose une panoplie d'outils : les développeurs, intégrateurs et administrateurs auront à leur disposition des fonctionnalités prêtes à l'emploi et simples d'utilisation.

La manipulation d'un formulaire peut être découpée en trois axes majeurs : sa **construction**, sa **validation à l'envoi**, et enfin **l'exploitation de ses données** une fois validé. Cette dernière partie est spécifique à chaque formulaire, tandis que la construction et la validation auront toujours des points communs entre différents formulaires : des champs identiques avec les mêmes règles de validation, le même affichage, etc.

En effet, il est très courant que des champs soient repris dans **différent formulaires** à travers **un même site** (adresse email, numéro de téléphone, etc.). Dans ce cas, il n'est pas souhaitable de devoir gérer la configuration de ces champs autant de fois qu'il y a de formulaires. Pour faciliter l'administration, l'extension se base sur une **configuration TypoScript** pour gérer les règles de validation de chaque formulaire.

-----

Comment ça fonctionne ?
-----------------------

La première étape est la construction du squelette du formulaire. Comme n'importe quel formulaire construit avec Extbase, il faudra à minima un **contrôleur**, un **modèle** contenant les données du formulaire, et enfin **l'architecture Fluid** (layouts, templates) permettant l'affichage du formulaire.

Une fois ce squelette mis en place, FormZ va pouvoir se brancher dessus : il faudra utiliser certaines classes, respecter quelques normes, puis écrire la configuration TypoScript associée au formulaire.

Lorsque tout sera branché correctement, l'extension s'occupera **automatiquement** de gérer l'affichage des champs et des erreurs, et procédera à la validation complète du formulaire. Vous n'aurez rien d'autre à faire.

-----

TL;DR
-----

« FormZ » est une extension permettant une manipulation aisée des formulaires sous TYPO3. Elle a plusieurs objectifs :

- **Facilité d'installation**

  La mise en place d'un formulaire fonctionnel est extrêmement **simple** et **rapide**.

- **Maintenabilité aisée**

  Grâce à une configuration **quasiment exclusivement basée sur TypoScript**, il est très simple de maintenir et faire évoluer un formulaire.

- **Regroupement des ressources**

  L'extension se charge d'automatiser un maximum de comportements. Elle va notamment **générer du code CSS et JavaScript** qui sera injecté directement dans la page.


-----

Example
-------

.. only:: html

    Exemple tiré du chapitre « :ref:`example` » :

    .. figure:: ../../Images/formz-example.gif
        :alt: Exemple FormZ

.. only:: latex

    Voir le chapitre « :ref:`example` ».

Aller plus loin
^^^^^^^^^^^^^^^

Bien que facile à mettre en place, FormZ reste également un outil **puissant et modulable**. Il permet une surcharge aisée de ses fonctionnalités, afin de permettre une adaptation propre à chaque formulaire. En effet, un formulaire peut très rapidement arriver à un stade où l'automatisation ne suffit plus, et des développements spécifiques sont alors requis.

FormZ propose une API pour répondre à ces besoins : il est possible de surcharger PHP et JavaScript pour manipuler le formulaire selon les besoins ; vous retrouverez toutes les informations nécessaires dans cette documentation.

.. only:: html

    Vous pouvez rester dans la présentation et en apprendre plus sur les sujets suivants :

.. toctree::
    :maxdepth: 5
    :titlesonly:

    TypoScriptConfiguration
    FieldsActivation
