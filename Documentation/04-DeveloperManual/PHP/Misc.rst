.. include:: ../../Includes.txt

.. _developerManual-php-misc:

Miscellaneous
=============

In this chapter you will find some features available with the extension.


Fetch the last invalid form
---------------------------

If you need to fetch the **instance of the last form which did not pass its validation rules**, you can use the function:

.. code-block:: php

    \Romm\Formz\Utility\FormUtility::getFormWithErrors($formClassName);

Where :php:`$formClassName` is the name of the class of the form model (for instance :php:`MyVendor\MyExtension\Form\ExampleForm`).

-----

Create a custom activation condition
------------------------------------

Coming soon :php:`Romm\Formz\Condition\ConditionFactory::registerCondition`
