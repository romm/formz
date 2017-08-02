.. include:: ../../../Includes.txt

.. _developerManual-php-misc:

Autres
======

Dans ce chapitre vous retrouverez quelques fonctionnalités disponibles dans l'extension.


Récupération du dernier formulaire invalide
-------------------------------------------

Si vous avez besoin de récupérer **l'instance du dernier formulaire qui n'a pas passé ses règles de validation**, vous pouvez utiliser la fonction :

.. code-block:: php

    \Romm\Formz\Utility\FormUtility::getFormWithErrors($formClassName);

Où :php:`$formClassName` est le nom de classe du modèle du formulaire (exemple :php:`MyVendor\MyExtension\Form\ExampleForm`).

-----

Redirection si formulaire inexistant
------------------------------------

Vous avez accès à une fonction qui vous permet de **rediriger l'action actuelle** si un **argument requis d'une action n'est pas rempli** : :php:`\Romm\Formz\Utility\FormUtility::onRequiredArgumentIsMissing()`

C'est notamment utile lorsqu'un utilisateur essaie d'accéder à l'action du contrôleur de soumission du formulaire. En effet, cette action n'est véritablement appelée que lorsque le formulaire envoyé est valide. Mais il est possible pour un utilisateur d'accéder à une URL qui appelle cette action, sans même soumettre le formulaire. En temps normal, cela peut engendrer une erreur fatale. Vous pouvez donc utiliser la fonction d'initialisation de l'action, qui est de la forme ``initializeActionName()``, et y appeler la fonction ci-dessus.

.. code-block:: php

    public function initializeSubmitFormAction()
    {
        FormUtility::onRequiredArgumentIsMissing(
            $this->arguments,
            $this->request,
            function($missingArgumentName) {
                $this->redirect('myIndex');
            }
        );
    }

    public function submitFormAction(FormExample $myForm)
    {
        // ...
    }

-----

Créer un type de condition d'activation personnalisé
----------------------------------------------------

À venir, cf. :php:`Romm\Formz\Condition\ConditionFactory::registerCondition`
