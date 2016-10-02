.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _developerManual-php-behaviour:

Comportement
============

.. only:: html

Pour savoir ce qu'est un comportement, rendez-vous au chapitre « :ref:`usersManual-typoScript-configurationBehaviours` ».

Exemple de comportement : mise en minuscule
-------------------------------------------

.. code-block:: php

    <?php
    namespace Romm\Formz\Behaviours;

    /**
     * Transforms a value in lowercase.
     *
     * @see \Romm\Formz\Behaviours\AbstractBehaviour
     */
    class ToLowerCaseBehaviour extends AbstractBehaviour {
        /**
         * @inheritdoc
         */
        public function applyBehaviour($value)
        {
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    $value[$key] = $this->applyBehaviour($val);
                }
            }
            else {
                $value = $this->applyBehaviourInternal($value);
            }

            return $value;
        }

        /**
         * Transforms the given value in lower case.
         *
         * @param  mixed    $value    The value.
         * @return string
         */
        protected function applyBehaviourInternal($value)
        {
            return mb_strtolower($value, 'UTF-8');
        }
    }
