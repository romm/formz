.. include:: ../../../Includes.txt

.. _developerManual-php-formValidator:

Validateur de formulaire
========================

Un validateur de formulaire est appelé lors de la **soumission** dudit formulaire : il permet de gérer toutes les règles de validations qui lui sont propres, et n'importe quel processus annexe qui dépendrait de ses données.

Le but de FormZ est d'automatiser un maximum de processus, notamment ceux de validation. Néanmoins il est probable que des formulaires complexes requièrent des **mécanismes plus poussés**, adaptés à leurs **besoins spécifiques**. Dans ce cas, il est possible de se brancher très facilement en plein cœur du processus de validation, et personnaliser son comportement en utilisant les fonctions listées ci-dessous.

.. tip::

    Si vous n'avez besoin d'aucun processus supplémentaire pour votre formulaire, vous pouvez utiliser le validateur par défaut fourni par FormZ : :php:`\Romm\Formz\Validation\Validator\Form\DefaultFormValidator`.

API
^^^

Un validateur de formulaire vous donne accès aux variables/fonctions suivantes :

- :ref:`$form <formValidator-form>`
- :ref:`$result <formValidator-result>`
- :ref:`beforeValidationProcess() <formValidator-beforeValidationProcess>`
- :ref:`*field*Validated() <formValidator-interValidationProcess>`
- :ref:`afterValidationProcess() <formValidator-afterValidationProcess>`

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
