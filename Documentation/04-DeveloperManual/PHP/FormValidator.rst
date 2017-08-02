.. include:: ../../Includes.txt

.. _developerManual-php-formValidator:

Form validator
==============

A form validator is called after the **submission** of the form: it allows managing all its specific validation rules, and any additional process which could depend on its data.

The goal of FormZ is to automatize a maximum of processes, especially the validation ones. However, it is likely that complex forms require **more advanced mechanism**, which fit their **specific needs**. In this case, it is possible to very easily hook into the core of the validation process, and customize its behaviour by using the functions listed below.

.. tip::

    If you do not need any additional process for your form, you can use the default validator provided by FormZ: :php:`\Romm\Formz\Validation\Validator\Form\DefaultFormValidator`.

API
^^^

A form validator gives you access to the following variables/functions:

- :ref:`$form <formValidator-form>`
- :ref:`$result <formValidator-result>`
- :ref:`beforeValidationProcess() <formValidator-beforeValidationProcess>`
- :ref:`*field*Validated() <formValidator-interValidationProcess>`
- :ref:`afterValidationProcess() <formValidator-afterValidationProcess>`

-----

.. _formValidator-form:

Form instance
-------------

.. container:: table-row

    Property
        .. code-block:: php

            protected $form;
    Type
        :php:`Romm\Formz\Form\FormInterface`
    Description
        In this variable is stored the **form instance** which is sent when the user launched the submission. You will find all the submitted data.

.. _formValidator-result:

Validation result
-----------------

.. container:: table-row

    Property
        .. code-block:: php

            protected $result;
    Type
        :php:`TYPO3\CMS\Extbase\Error\Result`
    Description
        In this variable is stored the **validation result**. You can interact with it according to your needs: especially adding/removing errors.

        This variable is returned to the controller at the end of the validation, which means if the result contains **at least one error**, the form will be **considered as invalid**.

.. _formValidator-beforeValidationProcess:

Pre-validation process
----------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            protected function beforeValidationProcess()
            {
               // ...
            }
    Return
        /
    Description
        This function is called **just before the launch of the form's fields validation process**. You may override it to configure your own behaviours: for instance the (de)activation of fields depending on your own criteria.

.. _formValidator-interValidationProcess:

During-validation process
-------------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            protected function *field*Validated()
            {
               // ...
            }
    Return
        /
    Description
        Every time the validation of a field ends, a function containing the name of the field is called. The function begins with the name of the field in lowerCamelCase, and ends with ``Validated`` (note the upper case ``V``).

        Example for the field ``firstName``, the name of the function will be ``firstNameValidated()``; if this function does exist in the class, it will be called, and you can then execute whatever you want.

.. _formValidator-afterValidationProcess:

Post-validation process
-----------------------

.. container:: table-row

    Fonction
        .. code-block:: php

            protected function afterValidationProcess()
            {
               // ...
            }
    Return
        /
    Description
        This function is called **just after the fields validation**. Override it to implement you own specific behaviours.

        Note that you can still use :php:`$this->result`.

-----

Form validation example
^^^^^^^^^^^^^^^^^^^^^^^

You can find below an example of a form validator.

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
