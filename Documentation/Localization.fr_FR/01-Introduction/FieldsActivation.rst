.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _fieldsActivation:

Activation des champs
=====================

FormZ met à disposition un principe simple d'utilisation, qui permettra d'éviter des développements répétitifs : l'activation des champs selon certaines conditions.

Principe
--------

Le principe d'activation peut s'appliquer directement **sur les champs d'un formulaire**, ou plus spécifiquement **sur les règles de validation d'un champ**. Le but est de n'activer le processus que si une certaine expression booléenne est vérifiée.

**Exemples :**

1. Un formulaire dispose d'un champ radio « Possédez-vous un animal de compagnie ? ».

   Si l'utilisateur coche « Oui », alors on souhaite connaître le nom de l'animal en question : un deuxième champ texte « Nom de l'animal » est activé, il apparaît et devient actif.

2. Un utilisateur peut renseigner son nom et son prénom, mais ces champs sont facultatifs.

   Si l'un des deux champs est rempli, alors on souhaite également connaître l'autre. On doit donc activer la règle de validation « required » d'un champ seulement si l'autre champ est rempli.

Dans les cas précédents, l'activation d'un champ signifie plusieurs choses :

1. Le champ devra être **affiché ou caché** selon s'il est activé ou non (le travail de CSS).
2. En JavaScript, les règles de validation du champ devront se lancer seulement si le champ est activé.
3. Pareil côté serveur, la validation du champ ne devra se lancer que si le champ est activé.

Ce système d'activation des champs présente un avantage majeur: FormZ sera capable d'automatiser les comportements souhaités, que ce soit avec PHP mais aussi avec JavaScript et CSS, en générant automatiquement du code, qui sera injecté directement dans la page et automatisera les comportements listés ci-dessus.

Cela évite d'avoir à écrire du code CSS/JavaScript/PHP pour chaque cas d'activation d'un champ, mais cela signifie également que les règles d'activation sont **concentrées à un seul endroit** (TypoScript) et non éparpillées dans des fichiers CSS/JavaScript/PHP.

-----

Fonctionnement
--------------

L'activation se configure grâce à deux propriétés : **des conditions d'activation**, ainsi qu'une **expression booléenne** – qui représente la logique de l'activation.

Les conditions
^^^^^^^^^^^^^^

Les conditions peuvent être configurées à deux endroits : soit à la racine de la configuration d'un formulaire, soit à la racine de la configuration d'un champ.

Potentiellement, n'importe quelle condition peut être vérifiée, tant qu'une implémentation de celle-ci existe en PHP. FormZ propose déjà plusieurs conditions basiques, telles que « :ref:`fieldHasValue <usersManual-typoScript-configurationActivation-fieldHasValue>` » ou « :ref:`fieldIsValid <usersManual-typoScript-configurationActivation-fieldIsValid>` » . Il est possible de créer de nouvelles conditions selon les besoins.

Vous pouvez retrouver les différentes conditions existantes et leur configuration au chapitre « :ref:`usersManual-typoScript-configurationActivation` ».

L'expression
^^^^^^^^^^^^

L'expression booléenne permet de lier différentes conditions par des opérateurs logiques : le « et » logique, le « ou » logique. Elle permet également de faire des groupements d'expressions grâce aux parenthèses.

**Exemple :**

``(colorIsRed || colorIsBlue) && emailIsValid``

Cette expression est vérifiée lorsque ``la couleur sélectionnée est rouge et que l'email est valide``, **ou** lorsque ``la couleur sélectionnée est bleu et que l'email est valide``.
