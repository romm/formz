.. include:: ../../Includes.txt

.. _typoScriptConfiguration:

Configuration TypoScript
========================

Une majeure partie de la mise en place et configuration d'un formulaire se fera via TypoScript.

Vous pouvez retrouver la liste des propriétés disponibles dans le chapitre « :ref:`usersManual-typoScript` ».

Pour avoir un aperçu de ce à quoi ressemble la configuration d'un formulaire fonctionnel, retrouvez ci-dessous un exemple pour un formulaire à cinq champs :

.. code-block:: typoscript

    config.tx_formz {
        forms {
            # Enregistrement du formulaire.
            Romm\FormzTemplate\Form\ExampleForm {
                # Conditions d'activation pouvant être utilisées par les champs.
                activationCondition {
                    # Condition vérifiée lorsque l'utilisateur indique posséder un diplôme.
                    hasCertificate {
                        type = fieldHasValue
                        fieldName = hasCertificate
                        fieldValue = 1
                    }
                }

                # Enregistrement des champs.
                fields {
                    # Champ : email
                    email {
                        # Requis + doit correspondre à une adresse email.
                        validation {
                            required < config.tx_formz.validators.required
                            isEmail < config.tx_formz.validators.email
                        }

                        # Transformation de la valeur en minuscule.
                        behaviours {
                            toLowerCase < config.tx_formz.behaviours.toLowerCase
                        }
                    }

                    # Champ : nom
                    name {
                        # Requis.
                        validation {
                            required < config.tx_formz.validators.required
                        }
                    }

                    # Champ : prénom
                    firstName {
                        # Requis.
                        validation {
                            required < config.tx_formz.validators.required
                        }
                    }

                    # Champ : possède un diplôme
                    hasCertificate {
                        # Requis.
                        validation {
                            required < config.tx_formz.validators.required
                        }
                    }

                    # Champ : nom du diplôme
                    certificateName {
                        # Requis.
                        validation {
                            required < config.tx_formz.validators.required
                        }

                        # Activation lorsque l'utilisateur indique posséder un diplôme.
                        activation {
                            condition = hasCertificate
                        }
                    }
                }
            }
        }
    }
