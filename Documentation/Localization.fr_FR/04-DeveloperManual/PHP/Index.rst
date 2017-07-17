.. include:: ../../../Includes.txt

.. _developerManual-php:

API — PHP
=========

.. tip::

    Vous pouvez retrouver un résumé des classes/fonctions PHP utilisables dans le chapitre « :ref:`cheatSheets-php` ».

Pour installer un formulaire, vous serez amené à utiliser les classes suivantes :

- :ref:`developerManual-php-model`

  :php:`Romm\Formz\Form\FormInterface` — devra être implémentée par le modèle de votre formulaire.

- :ref:`developerManual-php-formValidator`

  :php:`Romm\Formz\Validation\Validator\Form\AbstractFormValidator` — devra être héritée par le validateur de votre formulaire.

- :ref:`developerManual-php-validator`

  :php:`Romm\Formz\Validation\Validator\AbstractValidator` — devra être héritée par vos validateurs.

- :ref:`developerManual-php-behaviour`

  :php:`Romm\Formz\Behaviours\AbstractBehaviour` — devra être héritée par vos comportements.

.. toctree::
    :maxdepth: 5
    :titlesonly:
    :hidden:

    Model
    FormValidator
    Validator
    Behaviours
    Misc
