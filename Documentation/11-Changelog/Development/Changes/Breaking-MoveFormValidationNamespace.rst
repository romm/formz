.. include:: ../../../Includes.txt

================================================
Breaking: Move form validation classes namespace
================================================

Description
===========

The classes used for the form validation have been moved into the `Validation/Form` folder, as they are not proper validator classes. Only the
`DefaultFormValidator` class remains at its place.

This is an effort to clean up the files structure and make the whole application architecture more consistent.

Impact
======

The class :php:`\Romm\Formz\Validation\Validator\Form\AbstractFormValidator` has been moved to :php:`\Romm\Formz\Validation\Form\AbstractFormValidator`, meaning form validators that do extend this class must change the used namespace.

**Example:**

.. code-block:: php
    :linenos:
    :emphasize-lines: 4,13

    // OLD:
    namespace Vendor\MyExtension\Validation\Validator\Form;

    use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;

    class MyCustomFormValidator extends AbstractFormValidator
    {
    }

    // NEW:
    namespace Vendor\MyExtension\Validation\Validator\Form;

    use Romm\Formz\Validation\Form\AbstractFormValidator;

    class MyCustomFormValidator extends AbstractFormValidator
    {
    }
