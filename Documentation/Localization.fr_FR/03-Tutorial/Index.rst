.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _tutorial:

Guide : création d'un formulaire complet
========================================

Développement
^^^^^^^^^^^^^

Vous retrouverez ici toutes les étapes à suivre pour créer rapidement un nouveau formulaire.

Il est admis que vous travaillez sur une extension existante qui suit les conventions de Extbase.

Pour rappel, vous pouvez télécharger un exemple de formulaire fonctionnel dans le chapitre « :ref:`example` ».

Modèle de données
-----------------

Un formulaire n'est autre qu'un modèle de données. Il vous faut donc commencer par créer un modèle contenant toute sa logique, à savoir toutes les propriétés dont il est composé, ainsi que les « *getters* » et les « *setters* ».

Vous pouvez trouver un exemple de modèle de formulaire ici : « :ref:`developerManual-php-model` ».

.. note::

    Pour la suite de ce chapitre, nous admettrons que le modèle de formulaire se trouve au chemin suivant : :php:`\MyVendor\MyExtension\Form\MyForm`.

-----

Validateur de formulaire
------------------------

Si vous avez besoin de gérer des règles de validation poussées, vous devrez créer un validateur de formulaire.

**Exemple de règle de validation poussée** : votre formulaire permet aux utilisateurs de calculer une simulation de prix, qui sera générée en fonction des données envoyées. La simulation est calculée lorsque le formulaire est soumis avec des valeurs valides. Cependant, il se peut que pour des raisons aléatoires (erreur dans le calcul de l'estimation, service externe non disponible, etc.), le calcul de la simulation n'aboutisse pas. Dans un cas comme celui-ci, le validateur de formulaire devra rajouter une erreur au formulaire, afin de faire échouer la validation.

Vous pouvez vous inspirer de l'exemple de validateur de formulaire ici : « :ref:`developerManual-php-formValidator` ».

.. note::

    Pour la suite de ce chapitre, nous admettrons que le validateur de formulaire se trouve au chemin suivant : :php:`\MyVendor\MyExtension\Validation\Validator\Form\MyFormValidator`.

.. tip::

    Si vous n'avez besoin d'aucune règle de validation supplémentaire pour votre formulaire, vous pouvez utiliser le validateur de formulaire par défaut : :php:`\Romm\Formz\Validation\Validator\Form\DefaultFormValidator` — au lieu de créer un validateur de formulaire vide spécifiquement pour votre formulaire.

-----

Contrôleur
----------

Votre contrôleur contiendra généralement au moins deux actions :

* Une action d'affichage du formulaire, généralement « :php:`showFormAction` ». Tant que le formulaire n'aura **pas passé la validation**, cette action sera appelée.

.. code-block:: php

    public function showFormAction()
    {
        // ...
    }

* Une action de soumission du formulaire, généralement « :php:`submitFormAction` ». Cette méthode prend comme argument une instance du formulaire, et ne sera appelée **qu'une fois la validation passée**.

.. note::

    Pour que la validation prenne effet sur le formulaire, il faut rajouter l'annotation ``@validate`` dans le DocBlock de la fonction. Il s'agit d'une fonctionnalité fournie par Extbase, utilisée par Formz pour faciliter la validation du formulaire.

    Inspirez-vous de l'exemple ci-dessous.

.. code-block:: php

    /**
     * Action called when the form was submitted. If the form is not correct,
     * the request is forwarded to "showFormAction".
     *
     * @param    \MyVendor\MyExtension\Form\MyForm $form
     * @validate $form \MyVendor\MyExtension\Validation\Validator\Form\MyFormValidator
     */
     public function submitFormAction(MyForm $form)
     {
         // ...
     }

.. tip::

    Il est possible de raccourcir l'annotation ``@validate`` : ``@validate $form MyVendor.MyExtension:Form\MyFormValidator``.

Configuration TypoScript
^^^^^^^^^^^^^^^^^^^^^^^^

La gestion des règles de validation se fait en TypoScript.

Vous devrez donc suivre les indications du chapitre « :ref:`usersManual` » pour configurer correctement vos règles de validation.

**Exemple de configuration :**

.. code-block:: typoscript

    config.tx_formz {
        forms {
            MyVendor\MyExtension\Form\MyForm {
                activationCondition {
                    someFieldIsValid {
                        type = fieldIsValid
                        fieldName = someField
                    }
                }

                fields {
                    someField < config.tx_formz.fields.someField

                    someOtherField < config.tx_formz.fields.someField
                    someOtherField {
                        validation {
                            required < config.tx_formz.validators.required
                        }

                        activation.condition = someFieldIsValid
                    }
                }
            }
        }
    }

Intégration HTML + JavaScript
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

L'intégration suit les règles basiques de Fluid, vous devrez tout de même utiliser quelques ``ViewHelpers`` fournis par l'extension (consultez le chapitre « :ref:`integratorManual-viewHelpers` » pour la liste complète).

Pour la liste des fonctionnalités utilisables par un intégrateur, vous pouvez consulter le chapitre « :ref:`integratorManual` ».

Ci-dessous un exemple simple d'intégration de formulaire.

*my_extension/Resources/Private/Templates/MyController/ShowForm.html*

.. code-block:: html

    {namespace formz=Romm\Formz\ViewHelpers}

    <h1>Lorem Ipsum</h1>

    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ornare lorem vitae
    lacus efficitur, sed feugiat turpis tincidunt. Sed sed tellus ornare, pellentesque
    orci mollis, consequat eros.</p>

    <formz:form action="submitForm" name="exampleForm">
        <fieldset>
            <formz:field name="someField" layout="default">
                <formz:option name="label" value="Some Field" />

                <formz:section name="Field">
                    <f:form.textfield property="{fieldName}"
                                      id="{fieldId}" />
                </formz:section>
            </formz:field>

            <formz:field name="someOtherField" layout="default">
                <formz:option name="label" value="Some other Field" />

                <formz:section name="Field">
                    <f:form.textfield property="{fieldName}"
                                      id="{fieldId}" />
                </formz:section>
            </formz:field>
        </fieldset>
    </formz:form>
