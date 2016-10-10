.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _cheatSheets-php:

PHP cheat-sheets
================

Form model
----------

* Must implement :php:`Romm\Formz\Form\FormInterface` (see line 11);
* Can use the trait :php:`Romm\Formz\Form\FormTrait` to implement the required methods (see line 13);
* Must contain the “setters” and “getters” of each property.

**Example:**

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

Controller
----------

* Two actions are advised: one for the form displaying, one for its submission (see lines 11 & 21);
* The submission action must indicate the object type: the name of the PHP class of the form (see line 21);
* The submission action must indicate the form validator which will be used (see line 19).

**Example:**

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

Form validator
--------------

* Must inherit  :php:`Romm\Formz\Validation\Validator\Form\AbstractFormValidator` (see line 10);
* Can manipulate :php:`$this->result`, for instance to add errors (see line 65).
* Can override the methods :php:`beforeValidationProcess()` and :php:`afterValidationProcess()`, which are called before and after the validation process (see lines 24 & 58);
* Can call dynamic methods at the end of a field validation: :php:`*Validated` where ``*`` is the field name in lowerCamelCase (see line 43);
* Can de(activate) a field with the methods :php:`activateField()` and :php:`deactivateField()`  (see lines 31 & 32).

**Example:**

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
         * Before validation begins, we check if the user is still connected: if
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

Validators
----------

* It is advised to use validators which inherit :php:`Romm\Formz\Validation\Validator\AbstractValidator`; the main reason for that is to be able to use the property :php:`$javaScriptValidationFiles` (see line 4). Otherwise, basic Extbase validators work;
* Can associate JavaScript files with the property :php:`$javaScriptValidationFiles`: these files contain a code adaptation of the validator in JavaScript (see line 10);
* Can define messages which may be overridden in TypoScript with the property :php:`$supportedMessages` (see line 25);
* Also contains all basic Extbase validators features, like the property :php:`$supportedOptions` (see line 17).

**Example (from the core of Formz):**

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

Utilities
---------

The class :php:`Romm\Formz\Utility\FormUtility` contains methods that may help you during development:

Get form with errors
^^^^^^^^^^^^^^^^^^^^

The function :php:`getFormWithErrors` returns a form submitted during the last request, but which is not accessible from the controller because it contains errors.

**Example:**

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

Redirect an action if an argument is missing
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Using Extbase,  a user can try to access to the submission action, without actually submitting the form. For instance, he submits the form, then pastes the result URL in a new tab: Extbase will think the user submitted the form, but he didn't. In normal time, it would throw a fatal error.

Formz provides the function :php:`onRequiredArgumentIsMissing`, which will check that a required argument is missing, and run actions otherwise.

**Example:**

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
