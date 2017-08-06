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

Créer un type de condition d'activation personnalisé
----------------------------------------------------

À venir, cf. :php:`Romm\Formz\Condition\ConditionFactory::registerCondition`
