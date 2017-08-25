.. include:: ../../Includes.txt

.. _developerManual-php:

API — PHP
=========

.. tip::

    You can find a recap of the useful PHP classes/functions in the chapter “:ref:`cheatSheets-php`”.

To create a form, you will have to use the following classes:

- :ref:`developerManual-php-model`

  :php:`Romm\Formz\Form\FormInterface` — must be implemented by the model of your form.

- :ref:`developerManual-php-formValidator`

  :php:`Romm\Formz\Validation\Form\AbstractFormValidator` — must be inherited by your form validator.

- :ref:`developerManual-php-validator`

  :php:`Romm\Formz\Validation\Field\AbstractFieldValidator` — must be inherited by your validators.

- :ref:`developerManual-php-behaviour`

  :php:`Romm\Formz\Behaviours\AbstractBehaviour` — must be inherited by your behaviours.

.. toctree::
    :maxdepth: 5
    :titlesonly:
    :hidden:

    Model
    FormValidator
    Validator
    Behaviours
    Misc
