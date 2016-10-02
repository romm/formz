.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _developerManual-php-model:

Modèle d'un formulaire
----------------------

Un modèle de formulaire reprend exactement les même caractéristiques qu'un modèle classique de TYPO3 : une liste de propriétés propres au formulaire (exemple : ``email``, ``name``, ``firstName``), ainsi que les « getters » et « setters » de chacune de ces propriétés.

La principale différence est que votre modèle devra implémenter l'interface :php:`\Romm\Formz\Form\FormInterface`. Pour implémenter automatiquement les fonctions déclarées par cette interface, vous pouvez utiliser le trait :php:`\Romm\Formz\Form\FormTrait`.

-----

Exemple de modèle
^^^^^^^^^^^^^^^^^

Vous retrouverez ci-dessous un exemple de modèle de formulaire.


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
