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

Redirect if the form is not sent
--------------------------------

You have access to a function to **redirect the current action** if a **required argument for an action is not filled**: :php:`\Romm\Formz\Utility\FormUtility::onRequiredArgumentIsMissing()`

This is useful when a user tries to access the submit action of the form controller. Indeed, this action is called only when the submitted form is valid. But a user can still access a URL which calls this action, without even submitting the form. In a normal scope, it can throw a fatal error. You may then use the action initialization function, which looks like ``initializeActionName()``, and call the function above.

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

Create a custom activation condition
------------------------------------

Coming soon :php:`Romm\Formz\Condition\ConditionFactory::registerCondition`
