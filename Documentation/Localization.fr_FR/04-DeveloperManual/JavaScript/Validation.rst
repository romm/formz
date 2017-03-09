.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------

.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _developerManual-javaScript-validation:

Validation
==========

======================================================================================================== ==========================================================
Function                                                                                                 Description
======================================================================================================== ==========================================================
:ref:`Formz.Validation.registerValidator() <developerManual-javaScript-validation-registerValidator>`    Registers a new validator.
======================================================================================================== ==========================================================

.. _developerManual-javaScript-validation-registerValidator:

Register a validator
--------------------

.. container:: table-row

    Function
        ``Formz.Validation.registerValidator(name, callback)``
    Return
        /
    Parameters
        - ``name`` : nom du validateur, doit être unique. Si le validateur est l'implémentation JavaScript d'un validateur PHP, alors ``name`` devra être le nom de la classe PHP du validateur, par exemple ``Romm\Formz\Validation\Validator\RequiredValidator``.
        - ``callback`` : la fonction qui sera appelée lorsque le validateur sera utilisé pour savoir si une valeur est valide.
    Description
        Comme leur implémentation en PHP, les validateurs permettent à JavaScript de savoir si une valeur est valide ou non. Le but des validateurs JavaScript est de **donner instantanément l'indication à l'utilisateur sur la validité de la valeur renseignée**, sans avoir à attendre une validation du serveur.

        La plupart du temps, un validateur JavaScript est une **conversion JavaScript de l'algorithme d'un validateur PHP**.

        La logique du validateur se trouvera dans ``callback``, qui possède les propriétés ci-dessous :

        1. Il possède trois arguments :

           - ``value`` : la valeur qui doit être validée.
           - ``callback`` : la fonction qui **devra être appelée** lorsque le validateur aura fini son travail de validation.
           - ``states`` : un objet contenant les propriétés ci-dessous :

             - ``result`` : l'instance de résultat qui sera utilisée pour rajouter des erreurs si la validation ne passe pas.
             - ``configuration`` : le tableau de configuration du validateur.
             - ``data`` : une liste de propriétés pouvant servir dans la validation.
             - ``validatorName`` : le nom du validateur utilisé.

        2. Le validateur devra **dans tous les cas** appeler ``callback();``, car c'est de cette manière que FormZ détectera la fin du processus du validateur. **Ne pas l'appeler pourra faire planter tout le fonctionnement de FormZ.**

        3. Pour rajouter une erreur, il faudra passer par l'instance de résultat contenue dans ``states['result']``, cf. l'exemple plus bas.

        **Exemple de validateur :**

        .. code-block:: javascript

             Formz.Validation.registerValidator(
                 'Vendor\\Extension\\Validation\\Validator\\MyCustomValidator',
                 function (value, callback, states) {
                     if (value !== 'foo') {
                         states['result'].addError('default', states['configuration']['messages']['default']);
                     }

                     callback();
                 }
             );
