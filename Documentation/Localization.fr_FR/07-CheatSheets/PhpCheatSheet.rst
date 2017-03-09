.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _cheatSheets-php:

Anti-sèche PHP
==============

Modèle de formulaire
--------------------

* Doit implémenter :php:`Romm\Formz\Form\FormInterface` (cf. ligne 11) ;
* Peut utiliser le trait :php:`Romm\Formz\Form\FormTrait` pour implémenter les méthodes requises (cf. ligne 13) ;
* Doit contenir les « setters » et « getters » de chaque propriété.

**Exemple :**

.. code-block:: php
    :linenos:
    :emphasize-lines: 11,13

    <?php
    namespace MyVendor\MyExtension\Form;

    use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
    use Romm\Formz\Form\FormInterface;
    use Romm\Formz\Form\FormTrait;

    /**
     * Example form
     */
    class ExampleForm extends AbstractEntity implements FormInterface {

        use FormTrait;

        /**
         * @var string
         */
        protected $email;

        /**
         * @var string
         */
        protected $name;

        /**
         * @return string
         */
        public function getEmail(){
           return $this->email;
        }

        /**
         * @return string
         */
        public function getName(){
           return $this->name;
        }
    }

-----

Contrôleur
----------

* Présence de deux actions conseillée : une pour l'affichage du formulaire, une pour sa soumission (cf. lignes 11 & 21) ;
* L'action de soumission doit indiquer le type d'objet : le nom de la classe PHP du formulaire (cf. ligne 21) ;
* L'action de soumission doit indiquer le validateur de formulaire qui devra être utilisé (cf. ligne 19).

**Exemple :**

.. code-block:: php
    :linenos:
    :emphasize-lines: 11,20,22

    <?php
    namespace MyVendor\MyExtension\Controller;

    use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
    use MyVendor\MyExtension\Form\ExampleForm;

    class ExampleController extends ActionController {
        /**
         * Show an example form.
         */
        public function showFormAction()
        {
            // Do anything you need in here...
        }

        /**
         * Action called when the Example form is submitted.
         *
         * @param ExampleForm $exForm
         * @validate $exForm MyVendor.MyExtension:Form\ExampleFormValidator
         */
        public function submitFormAction(ExampleForm $exForm)
        {
            // The form is valid: should you save it? Process it? Your call!
        }
    }

-----

Validateur de formulaire
------------------------

* Doit hériter de :php:`Romm\Formz\Validation\Validator\Form\AbstractFormValidator` (cf. ligne 10) ;
* Peut manipuler :php:`$this->result` à la volée, par exemple pour rajouter des erreurs (cf. ligne 65) ;
* Peut surcharger les méthodes :php:`beforeValidationProcess()` et :php:`afterValidationProcess()`, appelées respectivement avant et après le processus de validation (cf. lignes 24 & 58) ;
* Peut appeler des méthodes dynamiques à chaque fin de validation d'un champ : :php:`*Validated` où ``*`` représente le nom du champ en lowerCamelCase (cf. ligne 43) ;
* Peut (dés)activer un champ à la volée avec les méthodes :php:`activateField()` et :php:`deactivateField()`  (cf. lignes 31 & 32).

**Exemple :**

.. code-block:: php
    :linenos:
    :emphasize-lines: 10,24,31,32,43,58,65

    <?php
    namespace MyVendor\MyExtension\Validation\Validator\Form;

    use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;
    use MyVendor\MyExtension\Utility\SimulationUtility;
    use MyVendor\MyExtension\Utility\SessionUtility;
    use MyVendor\MyExtension\Utility\GeographyUtility;
    use MyVendor\MyExtension\Form\SimulationForm;

    class ExampleFormValidator extends AbstractFormValidator {

        /**
         * @var SimulationForm
         */
        protected $form;

        /**
         * Before validation begins, we check the user is still connected: if
         * not he will be automatically redirected.
         *
         * We also activate the rule `required` for the fields `name` and `first
         * name` if at least one of them is filled.
         */
        protected function beforeValidationProcess()
        {
            SessionUtility::checkUserIsConnected();

            if (empty($this->form->getName())
                && empty($this->form->getFirstName())
            ) {
                $this->deactivateFieldValidator('name', 'required');
                $this->deactivateFieldValidator('firstName', 'required');
            }
        }

        /**
         * When the zip code has been validated, we try to fetch the city which
         * matches this code. If no city was find we do nothing: the city value
         * submitted by the user will be validated next. If we find a city, we
         * fill the form with this new value and deactivate the field `city`,
         * because it is no more a user value.
         */
        protected function zipCodeValidated()
        {
            $zipCode = $this->form->getZipCode();
            $city = GeographyUtility::getCityFromZipCode($zipCode);
            if (null !== $city) {
                $this->form->setCity($city);
                $this->deactivateField('city');
            }
        }

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

-----

Validateurs
-----------

* Il est conseillé d'utiliser des validateurs qui héritent de :php:`Romm\Formz\Validation\Validator\AbstractValidator` ; la principale raison est l'utilisation de la propriété :php:`$javaScriptValidationFiles` (cf. ligne 4). Sinon les validateurs « classiques » d'Extbase fonctionnent ;
* Peut associer des fichiers JavaScript avec la propriété :php:`$javaScriptValidationFiles` : ces fichiers contiendront une adaptation du code de validation en JavaScript (cf. ligne 10) ;
* Peut définir des messages qui pourront être surchargés en TypoScript grâce à la propriété :php:`$supportedMessages` (cf. ligne 25) ;
* Contient également toutes les fonctionnalités des validateurs « classiques » d'Extbase, comme la propriété :php:`$supportedOptions` (cf. ligne 17).

**Exemple (tiré du cœur de FormZ) :**

.. code-block:: php
    :linenos:
    :emphasize-lines: 4,10,17,25

    <?php
    namespace Romm\Formz\Validation\Validator;

    class NumberLengthValidator extends AbstractValidator
    {

        /**
         * @var array
         */
        protected static $javaScriptValidationFiles = [
            'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.NumberLength.js'
        ];

        /**
         * @var array
         */
        protected $supportedOptions = [
            'minimum' => [0, 'The minimum length to accept', 'integer'],
            'maximum' => [PHP_INT_MAX, 'The maximum length to accept', 'integer'],
        ];

        /**
         * @var array
         */
        protected $supportedMessages = [
            'default' => [
                'key'       => 'validator.form.number_length.error',
                'extension' => null
            ]
        ];

        /**
         * @inheritdoc
         */
        public function isValid($value)
        {
            $pattern = '/^[0-9]{' . $this->options['minimum'] . ',' . $this->options['maximum'] . '}$/';
            if (false === preg_match($pattern, $value)) {
                $this->addError(
                    'default',
                    1445862696,
                    [$this->options['minimum'], $this->options['maximum']]
                );
            }
        }
    }

-----

Utilitaires
-----------

La classe :php:`Romm\Formz\Utility\FormUtility` contient des fonctions pouvant être utiles dans vos développements :

Récupérer un formulaire avec erreurs
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

La fonction :php:`getFormWithErrors` permet de récupérer un formulaire soumis lors de la dernière requête, mais qui n'est pas accessible directement dans le contrôleur car il contenait des erreurs.

**Exemple :**

.. code-block:: php
    :emphasize-lines: 14

    <?php
    namespace MyVendor\MyExtension\Controller;

    use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
    use MyVendor\MyExtension\Form\ExampleForm;
    use Romm\Formz\Utility\FormUtility;

    class ExampleController extends ActionController {
        /**
         * Show an example form.
         */
        public function showFormAction()
        {
            $submittedForm = FormUtility::getFormWithErrors(ExampleForm::class);
            if (null !== $submittedForm) {
                $this->view->assign('submittedForm', $submittedForm);
            }
        }
    }

Rediriger une action si l'argument est manquant
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Avec Extbase, il est parfois possible qu'un utilisateur essaie d'accéder à une action de soumission de formulaire, sans pour autant avoir soumis le formulaire. Par exemple, il soumet le formulaire, puis rentre l'URL obtenue dans un nouvel onglet : Extbase croira que l'utilisateur a soumis un formulaire, pour autant ce dernier n'existe pas. En temps normal, cela renvoie une erreur fatale.

FormZ met à disposition la fonction :php:`onRequiredArgumentIsMissing`, qui permet de vérifier qu'un argument requis est manquant, et de lancer certaines actions si c'est le cas.

**Exemple :**

.. code-block:: php
    :emphasize-lines: 22

    <?php
    namespace MyVendor\MyExtension\Controller;

    use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
    use MyVendor\MyExtension\Form\ExampleForm;
    use Romm\Formz\Utility\FormUtility;

    class ExampleController extends ActionController {
        /**
         * Show an example form.
         */
        public function showFormAction()
        {
            // Do anything you need in here...
        }

        /**
         * Function called before `submitFormAction()`.
         */
        public function initializeSubmitFormAction()
        {
            FormUtility::onRequiredArgumentIsMissing(
                $this->arguments,
                $this->request,
                function() {
                    // If the argument `$exForm` is missing, we redirect to the
                    // action "showForm".
                    $this->redirect('showForm');
                }
            );
        }

        /**
         * Action called when the Example form is submitted.
         *
         * @param ExampleForm $exForm
         * @validate $exForm MyVendor.MyExtension:Form\ExampleFormValidator
         */
        public function submitFormAction(ExampleForm $exForm)
        {
            // The form is valid: should you save it? Process it? Your call!
        }
    }
