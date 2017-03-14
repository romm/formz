.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-configuration:

Configuration
=============

Pour que CSS et JavaScript soient capables de retrouver certains éléments dans le DOM HTML, il est primordial de respecter certaines règles d'intégration.

Veillez à bien les respecter lors de la :ref:`création de nouveaux layouts <integratorManual-layouts>`, ou FormZ pourrait être victime de gros dysfonctionnements.

Conteneur d'un champ
--------------------

Un champ devra toujours avoir un **conteneur**, qui notamment utilisé par FormZ pour **afficher ou masquer le bloc** entier selon certaines conditions.

.. code-block:: html
    :linenos:
    :emphasize-lines: 1

    <div fz-field-container="email">
        <label for="email">Email :</label>

        <f:form.textfield property="email" id="email" />
    </div>

Dans cet exemple, le premier élément ``<div>`` est considéré par FormZ comme le conteneur du champ ``email``.

Par défaut, l'attribut à utiliser est ``fz-field-container``, qui doit contenir le nom du champ. Il est possible de personnaliser l'attribut utilisé, cf. « :ref:`Sélecteur du conteneur du champ<fieldsSettings-fieldContainerSelector>` ».

-----

Conteneur des messages
----------------------

De la même manière que le conteneur de champ doit être indiqué, le conteneur de messages pour ce champ doit également être défini. Ainsi, FormZ pourra afficher ou masquer cet élément selon la présence de messages (principalement des erreurs) ou non.

Ce conteneur est divisé en deux parties : un conteneur global, et un conteneur pour la liste des messages. Par exemple :

.. code-block:: html
    :linenos:
    :emphasize-lines: 1,4

    <div fz-field-feedback-container="email">
        Ce champs contient au moins une erreur :

        <div fz-field-feedback-list="email">
            <f:form.validationResults for="exForm.email">
                <f:for each="{validationResults.errors}" as="error">
                    <fz:formatMessage message="{error}" />
                </f:for>
            </f:form.validationResults>
        </div>
    </div>

Dans cet exemple, le conteneur de messages est identifié par ``fz-field-feedback-container="email"``, et le conteneur de la liste des messages par ``fz-field-feedback-list="email"``. Si le champ ``email`` ne contient aucun message, le premier conteneur sera masqué.

Les attributs utilisés dans cet exemple sont ceux par défaut. Il est possible de les personnaliser, cf. « :ref:`Sélecteur du conteneur des messages <fieldsSettings-feedbackContainerSelector>` » et « :ref:`Sélecteur de la liste des messages <fieldsSettings-feedbackListSelector>` ».

-----

.. _integratorManual-configuration-messageTemplate:

Modèle d'un message
-------------------

Les messages sont gérés automatiquement par JavaScript et Fluid. Le fonctionnement est le suivant : lorsqu'un champ récupère un message, ce dernier sera enveloppé dans un modèle HTML, puis placé dans le conteneur de messages (cf. plus haut).

Bien entendu, il peut s'avérer nécessaire que le modèle de message diffère selon les plateformes, les chartes graphiques, et autres. Il est donc possible de personnaliser le modèle de message pour chaque champ. La valeur par défaut du modèle est :

.. code-block:: html

    <span class="js-validation-rule-#VALIDATOR# js-validation-type-#TYPE# js-validation-message-#KEY#">#MESSAGE#</span>

Dans le modèle, les valeurs suivantes sont remplacées dynamiquement :

* **#FIELD#** : le nom du champ concerné ;

* **#FIELD_ID#** : l'attribut « id » du champ. Notez que dans le cas des champs de type « radio » ou « checkbox » l'utilisation de ce marqueur est obsolète ;

* **#VALIDATOR#** : le nom de la règle de validation qui a entraîné le message. Par exemple, cela peut être ``required`` ;

* **#TYPE#** : le type de message, généralement une erreur (auquel cas la valeur sera ``error``) ;

* **#KEY#** : la clé du message renvoyé. La plupart du temps, il s'agira de ``default`` ;

* **#MESSAGE#** : le corps du message.

Vous pouvez personnaliser le modèle de message de plusieurs façon :

Configuration TypoScript
^^^^^^^^^^^^^^^^^^^^^^^^

Vous pouvez configurer en TypoScript la valeur du modèle, dans la configuration des champs : « :ref:`Modèle de message <fieldsSettings-messageTemplate>` ».

Notez que vous pouvez également modifier la valeur par défaut pour tous les champs : « :ref:`Configuration par défaut des champs <settingsDefaultFieldSettings>` ».

Bloc HTML
^^^^^^^^^

Vous pouvez insérer, directement dans votre template, un bloc HTML qui contiendra le modèle. Le conteneur du bloc devra posséder l'attribut ``fz-message-template="1"``.

Par convention, il est conseillé que ce conteneur soit une balise ``<script>`` de type ``text/template``.

**Exemple :**

.. code-block:: html

    <script type="text/template" fz-message-template="1">
        <li class="#TYPE#">#MESSAGE#</li>
    </script>

Si JavaScript détecte ce bloc, alors il utilisera le contenu comme modèle pour les messages.

Vous pouvez insérer ce bloc à deux endroits :

1. **Dans le conteneur d'un champ**

   Situé à cet endroit, le modèle ne sera utilisé QUE pour le champ en question.

2. **Dans le corps de la balise** ``<form>``

   Dans ce cas, ce modèle sera utilisé par défaut pour tous les champs du formulaire.

   Notez qu'un modèle situé dans le conteneur d'un champ primera sur celui contenu dans la balise ``<form>``.
