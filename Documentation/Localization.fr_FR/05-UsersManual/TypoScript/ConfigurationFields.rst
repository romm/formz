.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationFields:

Champs
======

Les champs sont les propriétés du modèle PHP d'un formulaire. Pour chaque champ qui a un comportement particulier (par exemple une règle de validation), vous devrez remplir sa configuration TypoScript.

.. hint::

    Par convention, dès qu'un nouveau champ « générique » est configuré, sa configuration devrait se trouver au chemin ``config.tx_formz.fields`` ; de cette manière, il pourra être réutilisé par un autre formulaire. Un bon exemple de champ générique est « email » : plusieurs formulaires sont susceptibles d'utiliser ce champ avec exactement la même configuration.

Propriétés
----------

Retrouvez ci-dessous la liste des paramètres utilisables par un champ.

======================================================================================= =====================================
Propriété                                                                               Titre
======================================================================================= =====================================
:ref:`validation <fieldsValidation>`                                                    Règles de validation du champ

:ref:`behaviours <fieldsBehaviours>`                                                    Comportements du champ

:ref:`activation.conditions <fieldsActivation-conditions>`                              Conditions d'activation

:ref:`activation.expression <fieldsActivation-expression>`                              Expression d'activation du champ

:ref:`settings.fieldContainerSelector <fieldsSettings-fieldContainerSelector>`          Sélecteur du conteneur du champ

:ref:`settings.messageContainerSelector <fieldsSettings-messageContainerSelector>`      Sélecteur du conteneur des messages

:ref:`settings.messageListSelector <fieldsSettings-messageListSelector>`                Sélecteur de la liste des messages

:ref:`settings.messageTemplate <fieldsSettings-messageTemplate>`                        Modèle de message
======================================================================================= =====================================

-----

.. _fieldsValidation:

Validation du champ
-------------------

.. container:: table-row

    Propriété
        ``validation``
    Requis ?
        Non
    Description
        Contient la liste des validateurs et leurs règles utilisées pour la validation du champ.

        Les validateurs seront évalués dans l'ordre de leur déclaration.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.fields.phoneNumber {
                validation {
                    # Le numéro de téléphone est requis.
                    required < config.tx_formz.validators.required

                    # Le numéro de téléphone doit être composé de 10 chiffres.
                    numberLength < config.tx_formz.validators.numberLength
                    numberLength.options {
                        minimum = 10
                        maximum = 10
                    }
                }
            }

        .. note::

            Notez que les configurations des validateurs sont récupérées directement de ``config.tx_formz.validators``. Cela empêche une redondance de configuration lorsque les validateurs sont utilisés à plusieurs endroits.

.. _fieldsBehaviours:

Comportements du champ
----------------------

.. container:: table-row

    Propriété
        ``behaviours``
    Requis ?
        Non
    Description
        Contient la liste des comportements propre au champ.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.fields.email {
                behaviours {
                    # On transforme l'adresse email en minuscule.
                    toLowerCase < config.tx_formz.behaviours.toLowerCase
                }
            }

        .. note::

            Notez que les configurations des comportements sont récupérées directement de ``config.tx_formz.behaviours``. Cela empêche une redondance de configuration lorsque les comportements sont utilisés à plusieurs endroits.

.. _fieldsActivation-conditions:

Conditions d'activation
-----------------------

.. container:: table-row

    Propriété
        ``activation.conditions``
    Requis ?
        Non
    Description
        Contient la liste des conditions d'activation qui seront utilisables par ce champ uniquement. Notez que cette liste sera fusionnée avec celle de la propriété ``activationCondition`` du formulaire, car son principe est exactement le même : voir « :ref:`Conditions d'activation <formActivationCondition>` ».

        **Exemple :**

        .. code-block:: typoscript

            activation {
                items {
                    colorIsGreen {
                        type = fieldHasValue
                        fieldName = color
                        fieldValue = green
                    }
                }
            }

.. _fieldsActivation-expression:

Activation du champ
-------------------

.. container:: table-row

    Propriété
        ``activation.expression``
    Requis ?
        Non
    Description
        Contient la condition d'activation du champ : une expression logique décrivant la ou les cas où le champ sera activé.

        Pour plus d'informations sur ce fonctionnement, consultez le chapitre « :ref:`usersManual-typoScript-configurationActivation` ».

        **Exemple :**

        .. code-block:: typoscript

            activation {
                condition = colorIsRed || colorIsBlue
            }

.. _fieldsSettings-fieldContainerSelector:

Sélecteur du conteneur du champ
-------------------------------

.. container:: table-row

    Propriété
        ``settings.fieldContainerSelector``
    Requis ?
        Non
    Description
        Représente le sélecteur CSS qui sera utilisé pour récupérer le conteneur contenant le champ. Par exemple, il peut s'agir d'un élément ``<fieldset>``.

        Notez que le marqueur ``#FIELD#`` sera dynamiquement remplacé par le nom du champ.

        La valeur par défaut de ce paramètre est : ``[fz-field-container="#FIELD#"]``.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            fieldContainerSelector = [fz-field-container="#FIELD#"]
                        }
                    }

                    firstName {
                        settings {
                            fieldContainerSelector = .names
                        }
                    }

                    lastName {
                        settings {
                            fieldContainerSelector = .names
                        }
                    }
                }
            }

        .. note::

            Vous pouvez regrouper différents champs en leur donnant le même sélecteur de conteneur, c'est ce qui est fait dans l'exemple ci-dessus.

.. _fieldsSettings-messageContainerSelector:

Sélecteur du conteneur des messages
-----------------------------------

.. container:: table-row

    Propriété
        ``settings.messageContainerSelector``
    Requis ?
        Non
    Description
        Représente le sélecteur CSS qui sera utilisé pour récupérer le conteneur des messages du champ.

        Notez que le marqueur ``#FIELD#`` sera dynamiquement remplacé par le nom du champ.

        La valeur par défaut de ce paramètre est : ``[fz-field-message-container="#FIELD#"]``.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            messageContainerSelector = #errors-email
                        }
                    }
                }
            }

.. _fieldsSettings-messageListSelector:

Sélecteur de la liste des messages
----------------------------------

.. container:: table-row

    Propriété
        ``settings.messageListSelector``
    Requis ?
        Non
    Description
        Représente le sélecteur CSS qui sera utilisé pour récupérer le bloc contenant les messages du champ. Il s'agit d'une seconde couche de sélection pour le conteneur des messages (``settings.messageContainerSelector``) : cela permet d'y rajouter des contenus HTML statiques qui ne seront pas nettoyés par JavaScript lors du rafraîchissement des messages.

        Notez que le marqueur ``#FIELD#`` sera dynamiquement remplacé par le nom du champ.

        La valeur par défaut de ce paramètre est : ``[fz-field-message-list="#FIELD#"]``.

        Si une valeur vide est indiquée, alors le conteneur des erreurs sera utilisé.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            messageListSelector =
                        }
                    }
                }
            }

.. _fieldsSettings-messageTemplate:

Modèle de message
-----------------

.. container:: table-row

    Propriété
        ``settings.messageTemplate``
    Requis ?
        Non
    Description
        Modèle HTML utilisé par JavaScript pour les messages.

        La valeur par défaut de ce paramètre est :

        .. code-block:: html

            <span class="js-validation-rule-#VALIDATOR# js-validation-type-#TYPE#
                  js-validation-message-#KEY#">#MESSAGE#</span>

        Dans le modèle, les valeurs suivantes sont remplacées dynamiquement :

        * **#FIELD#** : le nom du champ concerné ;

        * **#FIELD_ID#** : l'attribut « id » du champ. Notez que dans le cas des champs de type « radio » ou « checkbox » l'utilisation de ce marqueur est obsolète ;

        * **#VALIDATOR#** : le nom de la règle de validation qui a entraîné le message. Par exemple, cela peut être ``required`` ;

        * **#TYPE#** : le type de message, généralement une erreur (auquel cas la valeur sera ``error``) ;

        * **#KEY#** : la clé du message renvoyé. La plupart du temps, il s'agira de ``default`` ;

        * **#MESSAGE#** : le corps du message.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm {
                fields {
                    email {
                        settings {
                            messageTemplate = <li class="#TYPE#">#MESSAGE#</li>
                        }
                    }
                }
            }
