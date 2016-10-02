.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _developerManual-php-model:

Form model
----------

A form model has exactly the same characteristics as a class TYPO3 model: a list of properties for the form (example: ``email``, ``name``, ``firstName``), as well as the “getters” and “setters” of each property.

The main difference is that your model must implement the interface :php:`\Romm\Formz\Form\FormInterface`. To implement automatically the functions declared by this interface, you can use the trait :php:`\Romm\Formz\Form\FormTrait`.

-----

Model example
^^^^^^^^^^^^^

You can find below an example of a form model.

.. code-block:: php

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
         * @var string
         */
        protected $firstName;

        /**
         * @var bool
         */
        protected $hasCertificate;

        /**
         * @var string
         */
        protected $certificateName;

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

        /**
         * @return string
         */
        public function getFirstName(){
           return $this->firstName;
        }

        /**
         * @return bool
         */
        public function getHasCertificate(){
           return $this->hasCertificate;
        }

        /**
         * @return string
         */
        public function getCertificateName(){
           return $this->certificateName;
        }
    }
