.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _usersManual-typoScript-configurationValidators:

Validateurs
===========

Un validateur est une règle de gestion qui sera appliquée à un champ lors de la soumission du formulaire.

.. hint::

    Par convention, dès qu'un nouveau validateur « générique » est créé, sa configuration devrait se trouver dans ``config.tx_formz.validators`` ; de cette manière, il pourra être réutilisé par différents champs.

Propriétés
----------

Retrouvez ci-dessous la liste des paramètres utilisables par un validateur.

=========================================== =================================
Propriété                                   Titre
=========================================== =================================
\* :ref:`className <validatorsClassName>`   Nom de la classe

\* :ref:`options <validatorsOptions>`       Options

:ref:`messages <validatorsMessages>`        Messages

:ref:`activation <validatorsActivation>`    Activation du validateur

:ref:`useAjax <validatorsUseAjax>`          Utiliser la validation Ajax ?
=========================================== =================================

-----

.. _validatorsClassName:

Nom de la classe
----------------

.. container:: table-row

    Propriété
        ``className``
    Requis ?
        Oui
    Description
        Contient le nom de classe utilisée pour ce validateur.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.validators.myRule {
                className = MyVendor\MyExtension\Validator\MyRuleValidator
            }

.. _validatorsOptions:

Options du validateur
---------------------

.. container:: table-row

    Propriété
        ``options``
    Requis ?
        Dépend du validateur, certaines options peuvent être obligatoires et entraîner un dysfonctionnement si non renseignées.
    Description
        Contient les options qui seront transmises au validateur. Ces options sont pré-définies dans le validateur en question, et doivent donc être connues pour être utilisées correctement.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.validators.numberLength {
                className = NumberLength
                options {
                    # Taille minimale du champ.
                    minimum = 0
                    # Taille maximale.
                    maximum = 0
                }
            }

        .. note::

            Pour connaître les options d'un validateur, il faut aller lire la documentation de sa classe PHP (cf. le chapitre « :ref:`developerManual-php-validator` »)

.. _validatorsMessages:

Messages du validateur
----------------------

.. container:: table-row

    Propriété
        ``messages``
    Requis ?
        Non
    Description
        Permet de surcharger les messages du validateur. Un validateur peut avoir un ou plusieurs messages, et chacun est identifié par une clé, ``default`` étant par convention la clé par défaut.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.validators.numberLength {
                messages {
                    # Deux clés de messages : `default` et `test`.
                    default {
                        # Chemin vers la clé LLL du message.
                        key = validator.form.number_length.error
                        # Extension contenant le fichier LLL.
                        extension = formz
                    }
                    test {
                        # Si vous renseignez `value`, la valeur sera directement
                        # utilisée et le système ne cherchera pas de traduction.
                        value = Test de message !
                    }
                }
            }

.. _validatorsActivation:

Activation du validateur
------------------------

.. container:: table-row

    Propriété
        ``activation``
    Requis ?
        Non
    Description
        Il est possible d'activer un validateur seulement dans certains cas. Le principe est exactement le même que l'activation des champs, voir « :ref:`Conditions d'activation <fieldsActivation-conditions>` » et « :ref:`Activation du champ <fieldsActivation-expression>` ».

        Exemple — on active la règle ``required`` du champ ``passwordRepeat`` seulement lorsque le champ ``password`` est valide :

        .. code-block:: typoscript

            passwordRepeat {
                validation {
                    required < config.tx_formz.validators.required
                    required.activation {
                        items {
                            passwordIsValid {
                                type = fieldIsValid
                                fieldName = password
                            }
                        }

                        condition = passwordIsValid
                    }
                }
            }

.. _validatorsUseAjax:

Utiliser la validation Ajax
---------------------------

.. container:: table-row

    Propriété
        ``useAjax``
    Requis ?
        Non
    Description
        Si cette propriété est définie, alors une requête Ajax sera envoyée par JavaScript lorsqu'il devra tester ce validateur.

        Notez que si une adaptation JavaScript du validateur PHP existe (cf. « :ref:`developerManual-javaScript-validation-registerValidator` »), alors remplir cette propriété n'aura aucun effet et le validateur JavaScript sera utilisé à la place de l'Ajax.

        .. code-block:: typoscript

            myField {
                validation {
                    serverValidation {
                        className = MyVendor\MyExtension\Validation\Validator\MyValidator
                        useAjax = 1
                    }
                }
            }
