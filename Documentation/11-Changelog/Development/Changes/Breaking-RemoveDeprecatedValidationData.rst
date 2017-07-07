.. include:: ../../../Includes.txt

==========================================
Breaking: Remove validation data component
==========================================

Description
===========

Validation data is an old concept: managing form data from inside a validator is an anti-pattern.

Upcoming middlewares feature (coming in a next patch) will allow better flexibility and replace this feature.

Impact
======

The functionality has been entirely removed and the following methods can't be used anymore:

- :php:`Romm\Formz\Validation\Validator\AbstractValidator::getValidationData()`
- :php:`Romm\Formz\Validation\Validator\AbstractValidator::setValidationData()`
- :php:`Romm\Formz\Validation\Validator\AbstractValidator::setValidationDataValue()`
- :php:`Romm\Formz\Form\FormInterface::getValidationData()`
- :php:`Romm\Formz\Form\FormInterface::setValidationData()`

Migration
=========

You might set up your own service class which can be used to manipulate data between two validators.

An upcoming patch will introduce persistence for forms, and allow storing arbitrary data in database.
