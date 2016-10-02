.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _usersManual-tips:

Astuces
=======

Factoriser les configurations
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

TypoScript dispose d'une fonctionnalité très intéressante lorsqu'il s'agit de manipuler différents bouts de configuration, qui ont de grande chance de se répéter. En effet, il est possible d'injecter, à n'importe quel endroit dans l'arbre TypoScript, tout un bout de configuration existante : simplement en utilisant l'opérateur ``<`` suivi du chemin vers la configuration existante.

Ainsi, il est vivement conseillé d'injecter les configurations déjà écrites dans les autres propriétés TypoScript (cf. :ref:`usersManual-typoScript-configurationValidators`, :ref:`usersManual-typoScript-configurationBehaviours` et :ref:`usersManual-typoScript-configurationFields`), plutôt que de réécrire toute la configuration à chaque fois.

**Example de mauvais usage :**

.. code-block:: typoscript

    config.tx_formz.forms {
        MyVendor\MyExtension\Form\ExampleForm {
            fields {
                email {
                    validation {
                        isEmail {
                            className = MyVendor\MyExtension\Validation\Validator\IsEmail
                            messages.default.value = Rentrez une adresse email valide.
                        }
                        required {
                            className = Romm\Formz\Validation\Validator\Required
                        }
                    }
                    behaviours {
                        toLowerCase {
                            className = Romm\Formz\Behaviours\ToLowerCase
                        }
                    }
                }
            }
        }

        MyVendor\MyExtension\Form\SecondExampleForm {
            fields {
                email {
                    validation {
                        isEmail {
                            className = MyVendor\MyExtension\Validation\Validator\IsEmail
                            messages.default.value = Rentrez une adresse email valide.
                        }

                        required {
                            className = Romm\Formz\Validation\Validator\Required
                        }
                    }
                }
            }
        }
    }

**Example de bon usage :**

.. code-block:: typoscript

    config.tx_formz {
        validators {
            isEmail {
                className = MyVendor\MyExtension\Validation\Validator\IsEmail
                messages.default.value = Rentrez une adresse email valide.
            }
        }

        behaviours {
            toLowerCase {
                className = Romm\Formz\Behaviours\ToLowerCase
            }
        }

        forms {
            MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        validation {
                            isEmail < config.tx_formz.validators.isEmail
                            required < config.tx_formz.validators.required
                        }
                        behaviours {
                            toLowerCase < config.tx_formz.behaviours.toLowerCase
                        }
                    }
                }
            }

            MyVendor\MyExtension\Form\SecondExampleForm {
                fields {
                    email {
                        validation {
                            isEmail < config.tx_formz.validators.isEmail
                            required < config.tx_formz.validators.required
                        }
                    }
                }
            }
        }
    }
