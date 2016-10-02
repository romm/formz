.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _developerManual-php-formValidator:

Validateur de formulaire
========================

Un validateur de formulaire est appelé lors de la **soumission** dudit formulaire : il permet de gérer toutes les règles de validations qui lui sont propres, et n'importe quel processus annexe qui dépendrait de ses données.

Le but de Formz est d'automatiser un maximum de processus, notamment ceux de validation. Néanmoins il est probable que des formulaires complexes requièrent des **mécanismes plus poussés**, adaptés à leurs **besoins spécifiques**. Dans ce cas, il est possible de se brancher très facilement en plein cœur du processus de validation, et personnaliser son comportement en utilisant les fonctions listées ci-dessous.

.. tip::

    Si vous n'avez besoin d'aucun processus supplémentaire pour votre formulaire, vous pouvez utiliser le validateur par défaut fourni par Formz : :php:`\Romm\Formz\Validation\Validator\Form\DefaultFormValidator`.

API
^^^

Un validateur de formulaire vous donne accès aux variables/fonctions suivantes :

- :ref:`$form <formValidator-form>`
- :ref:`$result <formValidator-result>`
- :ref:`$deactivatedFields <formValidator-deactivatedFields>`
- :ref:`$deactivatedFieldsValidators <formValidator-deactivatedFieldsValidators>`
- :ref:`beforeValidationProcess() <formValidator-beforeValidationProcess>`
- :ref:`*field*Validated() <formValidator-interValidationProcess>`
- :ref:`afterValidationProcess() <formValidator-afterValidationProcess>`
- :ref:`deactivateField($fieldName) <formValidator-deactivateField>`
- :ref:`activateField($fieldName) <formValidator-activateField>`
- :ref:`deactivateFieldValidator($fieldName, $validatorName) <formValidator-deactivateFieldValidator>`
- :ref:`activateFieldValidator($fieldName, $validatorName) <formValidator-activateFieldValidator>`

-----

.. _formValidator-form:

Instance du formulaire
----------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $form;
    Type
        :php:`Romm\Formz\Form\FormInterface`
    Description
        Dans cette variable est stockée **l'instance du formulaire** envoyé lors de la soumission par l'utilisateur. Vous y retrouverez toutes les données soumises.

.. _formValidator-result:

Résultat de validation
----------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $result;
    Type
        :php:`TYPO3\CMS\Extbase\Error\Result`
    Description
        Dans cette variable est stocké le **résultat de validation**. Vous pouvez interagir avec selon vos besoins, notamment pour rajouter/supprimer des erreurs.

        Cette variable est renvoyée au contrôleur en fin de validation, ce qui signifie que si le résultat contient **au moins une erreur**, le formulaire sera **considéré comme invalide**.

.. _formValidator-deactivatedFields:

Liste des champs désactivés par défaut
--------------------------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $deactivatedFields = ['myProperty'];
    Type
        :php:`array`
    Description
        Surchargez et remplissez le tableau avec le nom des **champs désactivés par défaut**. Cela signifie que vous devrez réactiver ces champs vous-même via la fonction ``activateField()`` selon vos besoins.

        Notez que ce tableau est automatiquement rempli en fonction de la configuration des conditions d'activation de champs : les champs désactivés seront rajoutés automatiquement par Formz.

.. _formValidator-deactivatedFieldsValidators:

Liste des règles devalidation de champs désactivées par défaut
--------------------------------------------------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $deactivatedFieldsValidators = [
                'myProperty' => ['required']
            ];
    Type
        :php:`array`
    Description
        Surchargez et remplissez le tableau avec le nom des **règles de validation de champs désactivées par défaut**. Le premier niveau du tableau correspond au nom du champ, et contient un second tableau constitué de toutes les règles de validation désactivées.

        Pour réactiver ces validations, vous devrez utiliser la fonction ``activateFieldValidator()``.

        Notez que ce tableau est automatiquement rempli en fonction de la configuration des conditions d'activation des règles de validation des champs : les règles de validation désactivées seront rajoutées automatiquement par Formz.

.. _formValidator-beforeValidationProcess:

Processus pré-validation
------------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            protected function beforeValidationProcess()
            {
               // ...
            }
    Retour
        /
    Description
        Cette fonction sera appelée **juste avant le lancement de la validation des champs du formulaire**. Vous pouvez la surcharger pour configurer vos propres comportements : par exemple les (dés)activations de champs selon vos propres critères.

.. _formValidator-interValidationProcess:

Processus inter-validation
--------------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            protected function *field*Validated()
            {
               // ...
            }
    Retour
        /
    Description
        À chaque fois que la validation d'un champ se termine, une fonction comportant le nom de ce champ est appelée. La fonction commence par le nom du champ en lowerCamelCase, et se termine par ``Validated`` (notez le ``V`` majuscule).

        Example pour le champ ``firstName``, le nom de la fonction sera ``firstNameValidated()`` ; si cette fonction existe dans la classe, elle sera appelée, et vous pourrez y exécuter ce que vous souhaitez.

.. _formValidator-afterValidationProcess:

Processus post-validation
-------------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            protected function afterValidationProcess()
            {
               // ...
            }
    Retour
        /
    Description
        Cette fonction sera appelée **juste après la validation des champs**. Surchargez-la pour gérer des comportement spécifiques.

        Notez que vous pouvez encore utiliser :php:`$this->result`.

.. _formValidator-deactivateField:

Désactivation d'un champ
------------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            $this->deactivateField($fieldName);
    Retour
        /
    Paramètres
        - ``$fieldName`` : le nom du champ à désactiver.
    Description
        Permet de désactiver un champ (ses règles de validation ne s'appliqueront plus).

        .. note::

            Appeler cette fonction dans ``afterValidationProcess()`` n'aura aucun effet.

.. _formValidator-activateField:

Activation d'un champ
---------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            $this->activateField($fieldName);
    Retour
        /
    Paramètres
        - ``$fieldName`` : le nom du champ à activer.
    Description
        Permet de réactiver un champ.

        .. note::

            Appeler cette fonction dans ``afterValidationProcess()`` n'aura aucun effet.

.. _formValidator-deactivateFieldValidator:

Désactivation d'une règle de validation d'un champ
--------------------------------------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            $this->deactivateFieldValidator($fieldName, $validatorName);
    Retour
        /
    Paramètres
        - ``$fieldName`` : le nom du champ contenant la règle à désactiver.
        - ``$validatorName`` : le nom de la règle à désactiver.
    Description
        Permet de désactiver une certaine règle de validation pour le champ donné.

        .. note::

            Appeler cette fonction dans ``afterValidationProcess()`` n'aura aucun effet.

.. _formValidator-activateFieldValidator:

Activation d'une règle de validation d'un champ
-----------------------------------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            $this->activateFieldValidator($fieldName, $validatorName);
    Retour
        /
    Paramètres
        - ``$fieldName`` : le nom du champ contenant la règle à activer.
        - ``$validatorName`` : le nom de la règle à activer.
    Description
        Permet de réactiver une certaine règle de validation pour le champ donné.

        .. note::

            Appeler cette fonction dans ``afterValidationProcess()`` n'aura aucun effet.

-----

Exemple de validateur de formulaire
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Vous retrouverez ci-dessous un exemple de validateur de formulaire.

.. code-block:: php

    <?php
    namespace MyVendor\MyExtension\Validation\Validator\Form;

    use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;
    use MyVendor\MyExtension\Utility\SimulationUtility;
    use MyVendor\MyExtension\Form\SimulationForm

    class ExampleFormValidator extends AbstractFormValidator {

        /**
         * @var SimulationForm
         */
        protected $form;

        /**
         * If there was no error in the form submission, the simulation process
         * runs. If the simulation result contains errors, we cancel the form
         * validation.
         */
        protected function afterValidationProcess()
        {
            if (false === $this->result->hasErrors()) {
                $simulation = SimulationUtility::simulate($this->form);

                if (null === $simulation) {
                    $error = new Error('Simulation error!', 1454682865)
                    $this->result->addError($error);
                } else {
                    $this->form->setSimulationResult($simulation);
                }
            }
        }
    }
