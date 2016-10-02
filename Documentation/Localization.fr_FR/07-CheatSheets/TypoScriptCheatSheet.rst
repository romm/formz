.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _cheatSheets-typoScript:

Anti-sèche TypoScript
=====================

Ci-dessous, un arbre complet reprenant la quasi-totalité des configuration TypoScript utilisables.

Vous pouvez retrouvez tous les détails des propriétés au chapitre « :ref:`usersManual-typoScript` ».

-----

.. code-block:: typoscript

    # Toutes les configurations partiront de la racine `config.tx_formz`.
    config.tx_formz {
        settings {
            # Liste des paramètres donnés par défaut aux formulaires.
            defaultFormSettings {
                # Cette classe CSS sera donnée par défaut à la balise `<form>`
                # de chaque formulaire.
                defaultClass = test-class

                # Message d'erreur utilisé par défaut après la validation d'un
                # champ, si aucun n'est trouvé.
                defaultErrorMessage = LLL:EXT:extension/.../locallang.xlf:default_error
            }

            # Liste des paramètres donnés par défaut aux champs des formulaires.
            defaultFieldSettings {
                # Sélecteur du conteneur HTML du champ.
                fieldContainerSelector = .formz-field-#FIELD#

                # Sélecteur du conteneur HTML des messages.
                feedbackContainerSelector = .formz-messages-#FIELD#

                # Sélecteur du conteneur HTML de la liste des messages.
                feedbackListSelector = .formz-messages-list-#FIELD#

                # Modèle HTML utilisé pour les messages renvoyé par la
                # validation d'un champ.
                messageTemplate = <span class="formz-message-#TYPE#">#MESSAGE#</span>
            }
        }

        # Ici on retrouvera la liste des formulaires utilisant Formz.
        forms {
            # La clé représente le nom de classe PHP du modèle du formulaire.
            \Vendor\Extension\Form\MyForm {
                # Liste des conditions que pourront être utilisée par les champs
                # et les règles de validation.
                activationCondition {
                    # Cette condition sera vérifiée quand le champ `password`
                    # contiendra une valeur valide.
                    passwordIsValid {
                        type = fieldIsValid
                        fieldName = passWord
                    }
                }

                # La liste des champs du formulaire. Chaque propriété ci-dessous
                # doit également être présente dans la classe PHP.
                fields {
                    # La clé représente le nom du champ.
                    email {
                        # Liste des règles de validations associées à ce champ.
                        validation {
                            # Le champ doit être requis. Cette règle a déjà été
                            # défini, on peut donc la récupérer avec le bon
                            # chemin TypoScript.
                            required < config.tx_formz.validators.required
                            required.messages {
                                # On peut surcharger le message d'erreur renvoyé
                                # si le champ ne passe pas la validation.
                                default.value = Le champ email est requis !
                            }

                            # Une deuxième règle : la valeur rentrée doit être
                            # une adresse email valide.
                            isEmail < config.tx_formz.validators.email
                            isEmail.messages {
                                # On surcharge le message. Notez qu'au dessus on
                                # a utilisé `value` pour un message en dur, ici
                                # on utilise une référence pour le multilingue.
                                default {
                                    key = error_email
                                    extension = my_extension
                                }
                            }
                        }
                    }

                    # Un champ de mot de passe, qui est requis et doit contenir
                    # au minimum 8 caractères.
                    password {
                        validation {
                            required < config.tx_formz.validators.required

                            stringLength < required < config.tx_formz.validators.stringLength
                            stringLength.options {
                                minimum = 8
                                maximum = 128
                            }
                        }
                    }

                    # Un champ de répétition de mot de passe. Il n'est activé
                    # que lorsque le mot de passe de base est valide, et doit
                    # contenir exactement la même valeur.
                    passwordRepeat {
                        validation {
                            required < config.tx_formz.validators.required

                            isSamePassword < config.tx_formz.validators.equalsToField
                            isSamePassword.options {
                                field = password
                            }
                        }

                        # On active ce champ seulement lorsque le champ
                        # `password` est valide.
                        activation.condition = passwordIsValid
                    }
                }
            }

            \Vendor\Extension\Form\MyOtherForm {
                # ...
            }
        }
    }
