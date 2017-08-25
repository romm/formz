.. include:: ../../../Includes.txt

=================================================
Breaking: Move field validation classes namespace
=================================================

Description
===========

The classes used for the field validation have been moved into the `Validation/Field` folder.

The class name :php:`Romm\Formz\Validation\Validator\AbstractValidator` has been changed to :php:`Romm\Formz\Validation\Field\AbstractFieldValidator`.

This is an effort to clean up the files structure and make the whole application architecture more consistent.

Impact
======

The class :php:`Romm\Formz\Validation\Validator\AbstractValidator` has been moved to :php:`Romm\Formz\Validation\Field\AbstractFieldValidator`, meaning field validators that do extend this class must change the used namespace.

**Example:**

.. code-block:: php
    :linenos:
        :emphasize-lines: 4,13

        // OLD:
        namespace Vendor\MyExtension\Validation\Validator;

        use Romm\Formz\Validation\Validator\AbstractValidator;

        class MyCustomFieldValidator extends AbstractValidator
        {
        }

        // NEW:
        namespace Vendor\MyExtension\Validation\Validator\Form;

        use Romm\Formz\Validation\Field\AbstractFieldValidator;

        class MyCustomFormValidator extends AbstractFieldValidator
        {
        }
