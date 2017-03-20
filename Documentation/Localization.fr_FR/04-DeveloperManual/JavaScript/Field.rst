.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------

.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _developerManual-javaScript-field:

Champ
=====

Ci-dessous la liste des fonctions utilisables avec une **instance de champ** :

=========================================================================================================================== ====================================================================
Fonction                                                                                                                    Description
=========================================================================================================================== ====================================================================
:ref:`addActivationCondition() <developerManual-javaScript-field-addActivationCondition>`                                   Rajouter une condition d'activation.

:ref:`addActivationConditionForValidator() <developerManual-javaScript-field-addActivationConditionForValidator>`           Rajouter une condition d'activation pour une règle de validation.

:ref:`addValidation() <developerManual-javaScript-field-addValidation>`                                                     Rajouter une règle de validation.

:ref:`checkActivationCondition() <developerManual-javaScript-field-checkActivationCondition>`                               Vérifier l'activation d'un champ.

:ref:`checkActivationConditionForValidator() <developerManual-javaScript-field-checkActivationConditionForValidator>`       Vérifier l'activation d'un validateur d'un champ.

:ref:`getActivatedValidationRules() <developerManual-javaScript-field-getActivatedValidationRules>`                         Récupérer les règles de validation actives.

:ref:`getConfiguration() <developerManual-javaScript-field-getConfiguration>`                                               Récupérer la configuration du champ.

:ref:`getElement() <developerManual-javaScript-field-getElement>`                                                           Récupérer l'élément DOM du champ.

:ref:`getElements() <developerManual-javaScript-field-getElements>`                                                         Récupère la configuration complète.

:ref:`getMessageListContainer() <developerManual-javaScript-field-getMessageListContainer>`                                 Récupérer l'élément DOM du conteneur de messages de validation.

:ref:`getErrors() <developerManual-javaScript-field-getErrors>`                                                             Récupérer les erreurs.

:ref:`getFieldContainer() <developerManual-javaScript-field-getFieldContainer>`                                             Récupérer le conteneur du champ.

:ref:`getForm() <developerManual-javaScript-field-getForm>`                                                                 Récupérer le formulaire parent.

:ref:`getLastValidationErrorName() <developerManual-javaScript-field-getLastValidationErrorName>`                           Récupérer le nom de la dernière erreur de validation.

:ref:`getMessageTemplate() <developerManual-javaScript-field-getMessageTemplate>`                                           Récupérer le modèle de message.

:ref:`getName() <developerManual-javaScript-field-getName>`                                                                 Récupérer le nom du champ.

:ref:`getValue() <developerManual-javaScript-field-getValue>`                                                               Récupérer la valeur du champ.

:ref:`handleLoadingBehaviour() <developerManual-javaScript-field-handleLoadingBehaviour>`                                   Gérer le comportement de chargement.

:ref:`hasError() <developerManual-javaScript-field-hasError>`                                                               Vérifier la présence d'une erreur.

:ref:`insertErrors() <developerManual-javaScript-field-insertErrors>`                                                       Insérer des erreurs.

:ref:`isValid() <developerManual-javaScript-field-isValid>`                                                                 Vérifier la validité du champ.

:ref:`isValidating() <developerManual-javaScript-field-isValidating>`                                                       Vérifier que le processus de validation du champ tourne.

:ref:`onError() <developerManual-javaScript-field-onError>`                                                                 Détecter une erreur de validation.

:ref:`onValidationBegins() <developerManual-javaScript-field-onValidationBegins>`                                           Détecter le lancement de la validation.

:ref:`onValidationDone() <developerManual-javaScript-field-onValidationDone>`                                               Détecter la fin de la validation.

:ref:`refreshMessages() <developerManual-javaScript-field-refreshMessage>`                                                  Vider le conteneur de messages.

:ref:`validate() <developerManual-javaScript-field-validate>`                                                               Lancer le processus de validation.

:ref:`wasValidated() <developerManual-javaScript-field-wasValidated>`                                                       Le champ a été validé.
=========================================================================================================================== ====================================================================

.. _developerManual-javaScript-field-addActivationCondition:

Rajouter une condition d'activation
-----------------------------------

.. container:: table-row

    Fonction
        ``addActivationCondition(name, callback)``
    Retour
        /
    Paramètres
        - ``name`` (String) : nom arbitraire de la condition d'activation.
        - ``callback`` (Function) : la fonction qui sera appelée lorsque la condition d'activation sera lancée.
    Description
        Rajoute une condition d'activation pour le champ. Le fonctionnement est le suivant : lorsque JavaScript tente de valider le champ, il va boucler sur toutes les conditions d'activation enregistrées pour ce champ, et exécuter la fonction contenue dans ``callback``. Si au moins l'une d'entre elles retourne ``false``, alors le champ sera considéré comme désactivé.

        La fonction ``callback`` contiendra deux paramètres :

        - ``field`` : l'instance du champ qui est en train d'être vérifié ;
        - ``continueValidation`` : une fonction qui **devra impérativement** être appelé dans votre fonction, et qui contiendra **un seul et unique paramètre** : un booléen qui indiquera si oui ou non le champ est désactivé.

        .. warning::

            Soyez certain que ``continueValidation`` est appelé à coup sûr, cela peut entraîner d'énormes dysfonctionnements si ce n'est pas le cas.

        .. attention::

            Notez bien que si vous agissez avec JavaScript sur les situations dans lesquelles des champs sont (dés)activés, il faudra très probablement que vous fassiez en sorte d'avoir le même comportement côté serveur, dans le :ref:`developerManual-php-formValidator`, grâce aux fonctions :ref:`deactivateField($fieldName) <formValidator-deactivateField>` et :ref:`activateField($fieldName) <formValidator-activateField>`.

        **Exemple :**

        .. code-block:: javascript

            form.getFieldByName('email').addActivationCondition(
                'customConditionEmail',
                function (field, continueValidation) {
                    var flag = true;

                    if (jQuery('#some-random-element').hasClass('test')) {
                        flag = false;
                    }

                    continueValidation(flag);
                }
            );

        .. hint::

            Vous pouvez rajouter autant de conditions d'activation que souhaité.

        .. note::

            Cette fonction est appelée par le cœur de FormZ, dans du code généré automatiquement à partir des valeurs contenus dans la configuration TypoScript des :ref:`conditions d'activations de champs <fieldsActivation-expression>`.

.. _developerManual-javaScript-field-addActivationConditionForValidator:

Rajouter une condition d'activation pour une règle de validation
----------------------------------------------------------------

.. container:: table-row

    Fonction
        ``addActivationConditionForValidator(name, validationName, callback)``
    Retour
        /
    Paramètres
        - ``name`` (String) : nom arbitraire de la condition d'activation.
        - ``validationName`` (String) : le nom de la règle de validation à laquelle rajouter la condition d'activation.
        - ``callback`` (Function) : la fonction qui sera appelée lorsque la condition d'activation sera lancée.
    Description
        Il s'agit du même principe qu'au dessus, mais cette fois-ci pour une règle de validation.

        Par exemple, un champ peut contenir deux règles de validation, dont l'une peut être désactivée selon certains critères propres à votre environnement.

        .. attention::

            Notez bien que si vous agissez avec JavaScript sur les situations dans lesquelles des règles de validations de champs sont (dés)activés, il faudra très probablement que vous fassiez en sorte d'avoir le même comportement côté serveur, dans le :ref:`developerManual-php-formValidator`, grâce aux fonctions :ref:`deactivateFieldValidator($fieldName, $validatorName) <formValidator-deactivateFieldValidator>` et :ref:`activateFieldValidator($fieldName, $validatorName) <formValidator-activateFieldValidator>`.

        Dans l'exemple ci-dessous, on agit sur la règle ``required`` du champ ``email``. Il se peut donc que cette règle soit désactivée à certains moments ; pour autant un champ email aura probablement une autre règle ``isEmail`` qui vérifiera que la valeur est une adresse email valide : cette règle devra toujours être activée.

        **Exemple :**

        .. code-block:: javascript

            form.getFieldByName('email').addActivationConditionForValidator(
                'customConditionEmailRequired',
                'required',
                function (field, continueValidation) {
                    var flag = true;

                    if (customFunctionToCheckIfFieldsAreRequired()) {
                        flag = false;
                    }

                    continueValidation(flag);
                }
            );

        .. hint::

            Vous pouvez rajouter autant de conditions d'activation que souhaité.

        .. note::

            Cette fonction est appelée par le cœur de FormZ, dans du code généré automatiquement à partir des valeurs contenus dans la configuration TypoScript des :ref:`conditions d'activations de règles de validation <validatorsActivation>`.

-----

.. _developerManual-javaScript-field-addValidation:

Rajouter une règle de validation
--------------------------------

.. container:: table-row

    Fonction
        ``addValidation(validationName, validatorName, validationConfiguration)``
    Retour
        /
    Paramètres
        - ``validationName`` (String) : le nom de la règle (son index), doit être unique pour ce champ.
        - ``validatorName`` (String) : le nom du validateur utilisé pour cette règle. Cela doit être un validateur existant, rajouté via :ref:`Fz.Validation.registerValidator() <developerManual-javaScript-validation-registerValidator>`.
        - ``validationConfiguration`` (Object) : la configuration du validateur, qui lui sera transmise lorsque la validation sera lancée.
    Description
        Rajoute une règle de validation au champ.

        Il est ensuite possible de manipuler cette règle avec la fonction :ref:`addActivationConditionForValidator() <developerManual-javaScript-field-addActivationConditionForValidator>`.

-----

.. _developerManual-javaScript-field-checkActivationCondition:

Vérifier l'activation d'un champ
--------------------------------

.. container:: table-row

    Fonction
        ``checkActivationCondition(runValidationCallback, stopValidationCallback)``
    Retour
        /
    Paramètres
        - ``runValidationCallback`` (Function) : fonction qui sera appelée si le champ est activé.
        - ``stopValidationCallback`` (Function) : fonction qui sera appelée si le champ est désactivé.
    Description
        Cette fonction vous permet de vérifier que le champ est actuellement activé, ou non.

        Selon s'il est activé ou non, une des deux fonctions passées en paramètre sera appelée.

        **Exemple :**

        .. code-block:: javascript

            form.getFieldByName('email').checkActivationCondition(
                function() {
                    alert('Field is activated!');
                },
                function() {
                    alert('Field is deactivated!');
                }
            );

-----

.. _developerManual-javaScript-field-checkActivationConditionForValidator:

Vérifier l'activation d'un validateur d'un champ
------------------------------------------------

.. container:: table-row

    Fonction
        ``checkActivationConditionForValidator(validatorName, runValidationCallback, stopValidationCallback)``
    Retour
        /
    Paramètres
        - ``validatorName`` (String) : nom du validateur souhaité, par exemple ``required``.
        - ``runValidationCallback`` (Function) : fonction qui sera appelée si le validateur est activé.
        - ``stopValidationCallback`` (Function) : fonction qui sera appelée si le validateur est désactivé.
    Description
        Cette fonction vous permet de vérifier qu'un validateur donné du champ est actuellement activé, ou non.

        Selon s'il est activé ou non, une des deux fonctions passées en paramètre sera appelée.

        **Exemple :**

        .. code-block:: javascript

            form.getFieldByName('email').checkActivationCondition(
                'required',
                function() {
                    alert('The field is required!');
                },
                function() {
                    alert('The field is not required!');
                }
            );

-----

.. _developerManual-javaScript-field-getActivatedValidationRules:

Récupérer les règles de validation actives
------------------------------------------

.. container:: table-row

    Fonction
        ``getActivatedValidationRules(callback)``
    Retour
        /
    Paramètres
        - ``callback`` (Function) : fonction qui sera appelée lorsque la liste des règles de validation sera construite.
    Description
        Fonction permettant de récupérer la liste des règles de validation qui sont actuellement activées pour le champ.

        **Exemple :**

        .. code-block:: javascript

            form.getFieldByName('email').getActivatedValidationRules(
                function(activatedRules) {
                    if ('required' in activatedRules) {
                        // ...
                    }
                }
            );

-----

.. _developerManual-javaScript-field-getConfiguration:

Récupérer la configuration du champ
-----------------------------------

.. container:: table-row

    Fonction
        ``getConfiguration()``
    Retour
        ``Object``
    Description
        Retourne l'objet de configuration du champ, qui peut contenir certaines informations utiles.

-----

.. _developerManual-javaScript-field-getElement:

Récupérer l'élément DOM du champ
--------------------------------

.. container:: table-row

    Fonction
        ``getElement()``
    Retour
        ``Element``
    Description
        Retourne l'élément HTML (dans le DOM) du champ.

        .. attention::

            Cette fonction est à utiliser pour les champs qui ne contiennent qu'un seul élément, par exemple des champs de type ``select`` ou ``text``.

            Pour les champs à multiples éléments, comme les ``checkbox`` ou ``radio``, utilisez la fonction ``getElements()``.

-----

.. _developerManual-javaScript-field-getElements:

Récupérer les éléments DOM du champ
-----------------------------------

.. container:: table-row

    Fonction
        ``getElements()``
    Retour
        ``NodeList``
    Description
        Retourne les éléments HTML (dans le DOM) du champ.

        .. attention::

            Cette fonction est à utiliser pour les champs qui contiennent plusieurs éléments, par exemple des champs de type ``checkbox`` ou ``radio``.

            Pour les champs à élément unique, comme les ``select`` ou ``text``, utilisez la fonction ``getElement()``.

-----

.. _developerManual-javaScript-field-getMessageListContainer:

Récupérer l'élément DOM du conteneur de retours de validation
-------------------------------------------------------------

.. container:: table-row

    Fonction
        ``getMessageListContainer()``
    Retour
        ``Element``
    Description
        Le conteneur de retours de validation est un bloc qui sera automatiquement mis à jour par JavaScript, qui insérera les messages retournés par les différents validateurs utilisés.

        Il est déconseillé d'interagir directement sur le contenu de ce bloc, mais vous pouvez procéder à d'autres opérations comme le rajout de classes, par exemple.

        .. note::

            La valeur du paramètre TypoScript :ref:`settings.messageListSelector <fieldsSettings-messageListSelector>` sera utilisée pour sélectionner l'élément.

-----

.. _developerManual-javaScript-field-getErrors:

Récupérer les erreurs
---------------------

.. container:: table-row

    Fonction
        ``getErrors()``
    Retour
        ``Object``
    Description
        Retourne les erreurs actuelles du champ.

        .. warning::

            L'appel à cette fonction n'est logique que si le champ a déjà été validé. Référez-vous à la fonction :ref:`wasValidated() <developerManual-javaScript-field-wasValidated>` pour plus d'informations.

-----

.. _developerManual-javaScript-field-getFieldContainer:

Récupérer le conteneur du champ
-------------------------------

.. container:: table-row

    Fonction
        ``getFieldContainer()``
    Retour
        ``Element``
    Description
        Retourne l'élément DOM qui contient le gabarit entier du champ.

        .. note::

            La valeur du paramètre TypoScript :ref:`settings.fieldContainerSelector <fieldsSettings-fieldContainerSelector>` sera utilisée pour sélectionner l'élément.

-----

.. _developerManual-javaScript-field-getForm:

Récupérer le formulaire parent
------------------------------

.. container:: table-row

    Fonction
        ``getForm()``
    Retour
        ``Fz.FullForm``
    Description
        Retourne l'instance du formulaire dont fait partie ce champ.

-----

.. _developerManual-javaScript-field-getLastValidationErrorName:

Récupérer le nom de la dernière erreur de validation
----------------------------------------------------

.. container:: table-row

    Fonction
        ``getLastValidationErrorName()``
    Retour
        ``String``
    Description
        Retourne le nom de la dernière règle de validation qui a renvoyé une erreur lors de la validation du champ.

-----

.. _developerManual-javaScript-field-getMessageTemplate:

Récupérer le modèle de message
------------------------------

.. container:: table-row

    Fonction
        ``getMessageTemplate()``
    Retour
        ``String``
    Description
        Retourne le modèle de message à utiliser lorsqu'un message doit être rajouté dans le :ref:`conteneur de retours de validation <developerManual-javaScript-field-getMessageListContainer>`.

        Ce modèle peut être récupéré de deux façons :

        1. Via la configuration TypoScript :ref:`settings.messageTemplate <fieldsSettings-messageTemplate>` ;
        2. Via le :ref:`bloc HTML <integratorManual-configuration-messageTemplate>`.

-----

.. _developerManual-javaScript-field-getName:

Récupérer le nom du champ
-------------------------

.. container:: table-row

    Fonction
        ``getName()``
    Retour
        ``String``
    Description
        Retourne le nom du champ.

-----

.. _developerManual-javaScript-field-getValue:

Récupérer la valeur du champ
----------------------------

.. container:: table-row

    Fonction
        ``getValue()``
    Retour
        ``String|Array``
    Description
        Retourne la valeur actuelle du champ.

        Le type de retour diffère selon le type du champ. S'il s'agit d'un champ « simple » avec un seul élément (par exemple ``select`` ou ``text``), alors la valeur de ce champ sera renvoyée. En revanche, pour les champs « multiples » tels que ``checkbox`` ou ``radio``, un tableau contenant les valeurs sélectionnées sera retourné.

-----

.. _developerManual-javaScript-field-handleLoadingBehaviour:

Gérer le comportement de chargement
-----------------------------------

.. container:: table-row

    Fonction
        ``handleLoadingBehaviour(run)``
    Retour
        /
    Paramètres
        - ``run`` (Boolean) : ``true`` si le comportement de chargement doit être chargé, sinon ``false``.
    Description
        Activate ou désactive le comportement de chargement du champ. Concrètement, une classe CSS est ajoutée au conteneur du champ, ce qui permet de rajouter facilement en CSS un aspect visuel de chargement, comme un cercle de chargement à côté du champ en question.

        Par défaut, ce comportement est lancé lorsque la validation du champ commence, puis est stoppé lorsque la validation est terminée.

        Vous pouvez manipuler ce comportement à votre guise si vous effectuez des opérations « lourdes » sur votre champ, et que vous souhaitez indiquer à l'utilisateur que le processus est lancé (très utile pour les requêtes Ajax par exemple).

-----

.. _developerManual-javaScript-field-hasError:

Vérifier la présence d'une erreur
---------------------------------

.. container:: table-row

    Fonction
        ``hasError(validationName, errorName)``
    Retour
        ``Boolean``
    Paramètres
        - ``validationName`` (String) : nom de la règle de validation qui contiendrait une erreur.
        - ``errorName`` (String) : nom de l'erreur recherchée, couramment ``default``. Si la valeur donnée est ``null``, n'importe quelle erreur trouvée pour la règle de validation sera comptée.
    Description
        Vérifie la présence d'une certaine erreur pour le champ.

-----

.. _developerManual-javaScript-field-insertErrors:

Insérer des erreurs
-------------------

.. container:: table-row

    Fonction
        ``insertErrors(errors)``
    Retour
        /
    Paramètres
        - ``errors`` (Objet) : liste des erreurs à insérer dans le conteneur de messages.
    Description
        Insère des erreurs dans le conteneur de messages. La liste des erreurs est un objet dont le premier niveau représente le nom de la règle de validation, et le deuxième niveau le nom de l'erreur.

        **Exemple :**

        .. code-block:: javascript

            var errors = {customRule: {message: 'hello world!'}};
            form.getFieldByName('email').insertErrors(errors);

-----


.. _developerManual-javaScript-field-isValid:

Vérifier la validité du champ
-----------------------------

.. container:: table-row

    Fonction
        ``isValid()``
    Retour
        ``Boolean``
    Description
        Vérifie que le champ est valide.

        .. warning::

            L'appel à cette fonction n'est logique que si le champ a déjà été validé. Référez-vous à la fonction :ref:`wasValidated() <developerManual-javaScript-field-wasValidated>` pour plus d'informations.

-----

.. _developerManual-javaScript-field-isValidating:

Vérifier que le processus de validation du champ tourne
-------------------------------------------------------

.. container:: table-row

    Fonction
        ``isValidating()``
    Retour
        ``Boolean``
    Description
        Renvoie ``true`` si le processus de validation de ce champ est actuellement en train de tourner.

-----

.. _developerManual-javaScript-field-onError:

Détecter une erreur de validation
---------------------------------

.. container:: table-row

    Fonction
        ``onError(validationName, errorName, callback)``
    Retour
        /
    Paramètres
        - ``validationName`` (String) : nom de la règle de validation qui retournerait l'erreur.
        - ``errorName`` (String) : nom de l'erreur retournée, couramment ``default``. Si la valeur donnée est ``null``, n'importe quelle erreur déclenche l'évènement.
        - ``callback`` (Function) : fonction appelée lorsque la validation du champ rencontre l'erreur spécifiée par ``validationName`` et ``errorName``.
    Description
        Permet de brancher une fonction sur la rencontre d'une certaine erreur lors de la validation du champ.

-----

.. _developerManual-javaScript-field-onValidationBegins:

Détecter le lancement de la validation
--------------------------------------

.. container:: table-row

    Fonction
        ``onValidationBegins(callback)``
    Retour
        /
    Paramètres
        - ``callback`` (Function) : fonction appelée lorsque le processus de validation du champ commence.
    Description
        Permet de brancher une fonction sur le lancement du processus de validation du champ.

-----

.. _developerManual-javaScript-field-onValidationDone:

Détecter la fin de la validation
--------------------------------

.. container:: table-row

    Fonction
        ``onValidationDone(callback)``
    Retour
        /
    Paramètres
        - ``callback`` (Function) : fonction appelée lorsque le processus de validation du champ se termine.
    Description
        Permet de brancher une fonction sur la fin du processus de validation du champ.

-----

.. _developerManual-javaScript-field-refreshMessage:

Vider le conteneur de messages
------------------------------

.. container:: table-row

    Fonction
        ``refreshMessages()``
    Retour
        /
    Description
        Vide complètement le conteneur de messages. Vous pouvez insérer de nouveaux messages avec la fonction « :ref:`insertErrors(errors) <developerManual-javaScript-field-insertErrors>` ».

-----

.. _developerManual-javaScript-field-validate:

Lancer le processus de validation
---------------------------------

.. container:: table-row

    Fonction
        ``validate()``
    Retour
        /
    Description
        Lance le processus de validation du champ.

        Il est possible de se brancher sur le lancement de la validation avec la fonction :ref:`onValidationBegins() <developerManual-javaScript-field-onValidationBegins>`, et sur la fin de la validation avec la fonction :ref:`onValidationDone() <developerManual-javaScript-field-onValidationDone>`.

-----

.. _developerManual-javaScript-field-wasValidated:

Le champ a été validé
---------------------

.. container:: table-row

    Fonction
        ``wasValidated()``
    Retour
        ``Boolean``
    Description
        Retourne ``true`` si le champ a déjà fini un processus de validation au moins une fois.

        Cette fonction est notamment utile pour toutes les autres fonctions qui dépendent du résultat de la validation, comme la fonction :ref:`isValid() <developerManual-javaScript-field-isValid>`.
