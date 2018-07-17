.. include:: ../Includes.txt

.. _tutorial:

Guide: full creation of a form
==============================

Development
^^^^^^^^^^^

You will find here all the steps to follow to quickly create a new form.

It is assumed that you work on an existing extension which follows Extbase convention rules.

As a reminder, you can download an example of a working form in the chapter “:ref:`example`”.

Data model
----------

A form is no more than a data model. You should begin by creating the model containing all the logic, name all the properties it is composed of, as well as the “*getters*” and “*setters*”.

You can find an example of a form model here: “:ref:`developerManual-php-model`”.

.. note::

    For the rest of this chapter, we will admit that the form model stands at the following path: :php:`\MyVendor\MyExtension\Form\MyForm`.

-----

Form validator
--------------

If you need to manage advanced validation rules, you may need to create a form validator.

**Example of an advanced validation rule**: your form allows the users to calculate a price estimation, which is generated depending on the sent data. The simulation is calculated once the form is submitted with valid data. However, there can be random events (error during the estimation calculation, external service not available, etc.) which can prevent the simulation from succeeding. In a case like this one, the form validator will have to add an error to the form, in order to make the validation fail.

You can find an example of a form validator here : “:ref:`developerManual-php-formValidator`”.

.. note::

    For the rest of this chapter, we will admit that the form validator stands at the following path: :php:`\MyVendor\MyExtension\Validation\Validator\Form\MyFormValidator`.

.. tip::

    If you do not need more validation rule for your form, you can use the default form validator: :php:`\Romm\Formz\Validation\Validator\Form\DefaultFormValidator` — instead of creating an empty form validator specifically for you form.

-----

Controller
----------

Your controller will generally contain at least two actions:

* An action to display the form, generally “:php:`showFormAction`”. Until the form **does not** pass its validation, this action is called.

.. code-block:: php

    public function showFormAction()
    {
        // ...
    }

* An action for the form submission, generally “:php:`submitFormAction`”. This method takes as argument an instance of the form, and will be called **only once the validation passed**.

.. note::

    To enable validation for the form, the annotation ``@validate`` must be used in the function DocBlock. It is a feature provided by Extbase, and used by FormZ to ease the form validation.

    You can find an example below:

.. code-block:: php

    /**
     * Action called when the form was submitted. If the form is not correct,
     * the request is forwarded to "showFormAction".
     *
     * @param    \MyVendor\MyExtension\Form\MyForm $form
     * @validate $form \MyVendor\MyExtension\Validation\Validator\Form\MyFormValidator
     */
     public function submitFormAction(MyForm $form)
     {
         // ...
     }

.. tip::

    It is possible to shorten the ``@validate`` annotation: ``@validate $form MyVendor.MyExtension:Form\MyFormValidator``.

TypoScript configuration
^^^^^^^^^^^^^^^^^^^^^^^^

The handling of the validation rules is done with TypoScript.

You must follow the explanations of the chapter “:ref:`usersManual`” to configure correctly your validation rules.

**Configuration example:**

.. code-block:: typoscript

    config.tx_formz {
        forms {
            MyVendor\MyExtension\Form\MyForm {
                activationCondition {
                    someFieldIsValid {
                        type = fieldIsValid
                        fieldName = someField
                    }
                }

                fields {
                    someField < config.tx_formz.fields.someField

                    someOtherField < config.tx_formz.fields.someField
                    someOtherField {
                        validation {
                            required < config.tx_formz.validators.required
                        }

                        activation.expression = someFieldIsValid
                    }
                }
            }
        }
    }

HTML + JavaScript integration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Integration follows Fluid basic rules, but you will have to use some ``ViewHelpers`` provided by the extension (see the chapter “:ref:`integratorManual-viewHelpers`” for the full list).

The full list of features available to an integrator can be found in the chapter “:ref:`integratorManual`”.

Below is a basic example of a form integration:

*my_extension/Resources/Private/Templates/MyController/ShowForm.html*

.. code-block:: html

    {namespace fz=Romm\Formz\ViewHelpers}

    <h1>Lorem Ipsum</h1>

    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ornare lorem vitae
    lacus efficitur, sed feugiat turpis tincidunt. Sed sed tellus ornare, pellentesque
    orci mollis, consequat eros.</p>

    <fz:form action="submitForm" name="exampleForm">
        <fieldset>
            <fz:field name="someField" layout="default">
                <fz:option name="label" value="Some Field" />

                <fz:slot name="Field">
                    <f:form.textfield property="{fieldName}"
                                      id="{fieldId}" />
                </fz:slot>
            </fz:field>

            <fz:field name="someOtherField" layout="default">
                <fz:option name="label" value="Some other Field" />

                <fz:slot name="Field">
                    <f:form.textfield property="{fieldName}"
                                      id="{fieldId}" />
                </fz:slot>
            </fz:field>
        </fieldset>
    </fz:form>
