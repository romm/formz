.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _integratorManual-layouts:

Layouts
=======

Lors de l'intégration d'un formulaire, une problématique revient souvent : mettre en place des champs qui ont **exactement le même gabarit**, c'est à dire que le conteneur d'un champ sera **quasiment identique d'un champ à l'autre**. Dans la majorité des cas, deux champs répondront exactement aux même règles d'intégration HTML/CSS, ce qui entraînera une répétition de code importante, et brisera la règle du « *Don't Repeat Yourself* ».

Problématique
-------------

En quoi la répétition de blocs de code est gênante ?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Une des premières raisons est évidente : cela rend la **lecture du code plus compliquée**. Il est possible qu'un seul champ soit constitué de plusieurs niveaux de balises HTML : si on multiplie par le nombre de champs dans un formulaire, on peut vite arriver à des centaines de lignes de code.

La mise en place d'un nouveau champ est également lente : adapter le conteneur au champ, et changer toutes les variables liées au champ : libellé, attributs (``class``, ``id``), etc.

Mais enfin, et surtout, la **maintenabilité devient plus compliquée**. En effet, si une modification de l'architecture HTML doit être apportée à tous les champs d'un formulaire, il faudra appliquer le correctif sur chacun des champs. Pire : si un site possède des dizaines de formulaires basé sur le même modèle, il faudra modifier chacun de ces formulaires.

Solution
^^^^^^^^

Pour répondre à cette problématique, FormZ permet de **mutualiser les gabarits de champs dans des vues indépendantes**. Le code requis pour intégrer un champ est ainsi **beaucoup moins dense et plus compréhensible, mais également plus facilement maintenable** : lorsqu'une modification est effectuée dans le gabarit d'un champ, elle sera répercutée automatiquement sur tous les champs.

-----

Utilisation
-----------

Déclarer un nouveau layout
^^^^^^^^^^^^^^^^^^^^^^^^^^

Pour déclarer un nouveau layout utilisable par le ViewHelper :ref:`integratorManual-viewHelpers-field`, vous devrez commencer par **écrire sa configuration TypoScript**, puis créer le **fichier de template associé** et enfin créer le **fichier de layout**.

Vous serez ensuite en mesure d'utiliser votre nouveau layout avec n'importe quel champ de formulaire.

Configuration TypoScript
""""""""""""""""""""""""

La configuration TypoScript de votre layout devra faire partie d'un **groupe de layout**. FormZ propose par défaut le groupe ``default``.

.. tip::

    Dans un projet, il est conseillé d'utiliser un nom de groupe spécifique à ce projet, par exemple ``my-project``, ou ``my-company``.

Vous pouvez retrouver toutes les informations nécessaires dans le chapitre « :ref:`viewLayouts` ».

**Exemple :**

.. code-block:: typoscript

    config.tx_formz.view.layouts {
        my-project {
            templateFile = EXT:extension/Resources/Private/Templates/MyProject/Default.html
            items {
                one-column.layout = MyProject/OneColumn
                two-columns.layout = MyProject/TwoColumns
            }
        }
    }

Création du fichier de template
"""""""""""""""""""""""""""""""

Le fichier de template déclaré dans la propriété ``templateFile`` de la configuration TypoScript doit être créé, s'il n'existe pas encore. Dans la mesure du possible, il faudra mutualiser ce template avec un maximum de layouts.

Il est conseillé de découper votre template en plusieurs sections, contenant les blocs importants qui seront utilisés par votre layout. Par défaut, trois sections sont utilisées : ``Label``, ``Field`` et ``Feedback``.

Les variables suivantes sont utilisables directement dans votre template :

* ``layout`` : contient le chemin vers le layout utilisé, par exemple ``MyProject/OneColumn``. Vous pouvez l'utiliser avec le ViewHelper ``f:layout``.

* ``formName`` : le nom du formulaire actuel (la valeur de la propriété ``name`` utilisée dans le ViewHelper :ref:`integratorManual-viewHelpers-form`).

* ``fieldName`` : le nom du champ actuel (la valeur de la propriété ``name`` utilisée dans le ViewHelper :ref:`integratorManual-viewHelpers-field`).

* ``fieldId`` : si cet argument n'a pas été déclaré précédemment, il sera automatiquement rempli par un identifiant généré en fonction du nom du formulaire et du nom du champ. Exemple pour le champ ``email`` du formulaire ``monFormulaire`` : ``fz-mon-formulaire-email``.

.. important::

    Afin d'être entièrement fonctionnel avec FormZ, votre template devra respecter toutes les règles décrites dans le chapitre « :ref:`integratorManual-configuration` ».

**Exemple :**

*EXT:extension/Resources/Private/Templates/MyProject/Default.html*

.. code-block:: html

    <f:layout name="{layout}" />

    {namespace formz=Romm\Formz\ViewHelpers}

    <f:section name="Label">
        <label class="{f:if(condition: '{required}', then: 'required')}" for="{fieldId}">{label}</label>
    </f:section>

    <f:section name="Field">
        <div fz-field-container="{fieldName}">
            <formz:slot.render slot="Field" />
        </div>
    </f:section>

    <f:section name="Feedback">
        <div fz-field-feedback-container="{fieldName}">
            <div fz-field-feedback-list="{fieldName}">
                <f:for each="{validationResults.errors}" iteration="iteration" as="error">
                    <formz:formatMessage message="{error}" />
                </f:for>
            </div>
        </div>
    </f:section>


Création du fichier de layout
"""""""""""""""""""""""""""""

Le fonctionnement du layout vis à vis du template reste exactement le **même que dans une intégration Fluid classique** : vous pouvez y appeler les sections définies dans le template.

Servez-vous du layout pour effectuer le découpage souhaité, qui pourra ensuite être utilisé par les champs des formulaires (cf. plus bas).

**Exemple :**

*EXT:extension/Resources/Private/Layouts/MyProject/OneColumn.html*

.. code-block:: html

    <div class="row">
        <div class="col-md-4">
            <f:render section="Label" arguments="{_all}" />
        </div>
        <div class="col-md-4">
            <f:render section="Field" arguments="{_all}" />
        </div>
        <div class="col-md-4">
            <f:render section="Feedback" arguments="{_all}" />
        </div>
    </div>

Utiliser un layout
^^^^^^^^^^^^^^^^^^

Une fois que vous aurez enregistré vos layouts, vous serez en mesure de les utiliser avec le ViewHelper :ref:`integratorManual-viewHelpers-field` en remplissant l'attribut ``layout``. Le champ utilisera alors le layout voulu pour son rendu, avec les options choisies.

Ainsi, le rendu même du champ reste complètement externe à l'intégration de votre formulaire.

Pour en savoir plus, consultez le chapitre : « :ref:`integratorManual-viewHelpers-field` ».

**Exemple :**

.. code-block:: html
    :linenos:
    :emphasize-lines: 5

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:form action="submitForm" name="myForm">

        <formz:field name="email" layout="my-project.one-column">
            <formz:option name="required" value="1" />

            <formz:slot name="Field">
                <f:form.textfield property="{fieldName}" id="{fieldId}" placeholder="email" />
            </formz:slot>
        </formz:field>

    </formz:form>
