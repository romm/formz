.. include:: ../../../Includes.txt

.. _developerManual-javaScript-form:

Formulaire
==========

Ci-dessous la liste des fonctions utilisables avec une **instance de formulaire** :

=============================================================================== ==========================================================
Function                                                                        Description
=============================================================================== ==========================================================
:ref:`getConfiguration() <developerManual-javaScript-form-getConfiguration>`    Récupère la configuration complète.

:ref:`getElement() <developerManual-javaScript-form-getElement>`                Récupère l'élément DOM du formulaire.

:ref:`getFieldByName() <developerManual-javaScript-form-getFieldByName>`        Récupère un champ donné.

:ref:`getFields() <developerManual-javaScript-form-getFields>`                  Récupère tous les champs de ce formulaire.

:ref:`getName() <developerManual-javaScript-form-getName>`                      Récupère le nom du formulaire.

:ref:`onSubmit() <developerManual-javaScript-form-onSubmit>`                    Branche une fonction sur la soumission du formulaire.
=============================================================================== ==========================================================

.. _developerManual-javaScript-form-getConfiguration:

Récupérer la configuration
--------------------------

.. container:: table-row

    Fonction
        ``getConfiguration()``
    Retour
        ``Array``
    Description
        Récupère la configuration complète du formulaire. Il s'agit en grande partie de la configuration TypoScript, vous pouvez donc récupérer certaines valeurs que vous aurez personnalisé au préalable.

        **Exemple :**

        .. code-block:: javascript

            var formConfiguration = form.getConfiguration();
            var message = formConfiguration['settings']['defaultErrorMessage'];

-----

.. _developerManual-javaScript-form-getElement:

Récupérer l'élément
-------------------

.. container:: table-row

    Fonction
        ``getElement()``
    Retour
        ``HTMLFormElement``
    Description
        Récupère l'élément DOM du formulaire. Vous pouvez ensuite le manipuler selon vos besoins.

        **Exemple :**

        .. code-block:: javascript

            var formElement = form.getElement();
            formElement.classList.add('some-class');

-----

.. _developerManual-javaScript-form-getFieldByName:

Récupérer un champ donné
------------------------

.. container:: table-row

    Fonction
        ``getFieldByName(name)``
    Retour
        ``Fz.FullField``
    Paramètres
        - ``name`` : le nom du champ.
    Description
        Récupère un champ donné, que vous pouvez ensuite manipuler à votre guise.

        **Exemple :**

        .. code-block:: javascript

            var fieldEmail = form.getFieldByName('email');

-----

.. _developerManual-javaScript-form-getFields:

Récupérer tous les champs
-------------------------

.. container:: table-row

    Fonction
        ``getFields()``
    Retour
        ``Object<Fz.FullField>``
    Description
        Récupère tous les champs de ce formulaire.

        **Exemple :**

        .. code-block:: javascript

            var fields = form.getFields();
            for (var fieldName in fields) {
                // ...
            }

-----

.. _developerManual-javaScript-form-getName:

Récupérer le nom du formulaire
------------------------------

.. container:: table-row

    Fonction
        ``getName()``
    Retour
        ``String``
    Description
        Récupère le nom du formulaire.

        **Exemple :**

        .. code-block:: javascript

            var message = 'The form ' + form.getName() + ' has been submitted.';

-----

.. _developerManual-javaScript-form-onSubmit:

Se brancher sur la soumission du formulaire
-------------------------------------------

.. container:: table-row

    Fonction
        ``onSubmit(callback)``
    Retour
        /
    Paramètres
        - ``callback`` : fonction appelée à la soumission du formulaire. Si elle retourne false, la soumission du formulaire sera annulée.
    Description
        Branche une fonction sur la soumission du formulaire. Notez que la fonction ne sera pas appelée si la soumission du formulaire est bloquée (à cause d'un champ invalide par exemple).

        La fonction peut renvoyer ``false`` si la soumission doit être bloquée pour une raison quelconque.

        **Exemple :**

        .. code-block:: javascript

            form.onSubmit(function() {
                var foo = bar();
                if (true === foo) {
                    return false;
                }
            });
