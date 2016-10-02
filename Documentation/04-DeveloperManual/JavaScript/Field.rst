.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------

.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _developerManual-javaScript-field:

Field
=====

You can find below the list of available function for a **field instance**:

=========================================================================================================================== ====================================================================
Function                                                                                                                    Description
=========================================================================================================================== ====================================================================
:ref:`addActivationCondition() <developerManual-javaScript-field-addActivationCondition>`                                   Add an activation condition.

:ref:`addActivationConditionForValidator() <developerManual-javaScript-field-addActivationConditionForValidator>`           Add an activation condition to a given validation rule.

:ref:`addValidation() <developerManual-javaScript-field-addValidation>`                                                     Add a validation rule.

:ref:`checkActivationCondition() <developerManual-javaScript-field-checkActivationCondition>`                               Check a field activation.

:ref:`checkActivationConditionForValidator() <developerManual-javaScript-field-checkActivationConditionForValidator>`       Check the activation of a field validator.

:ref:`getActivatedValidationRules() <developerManual-javaScript-field-getActivatedValidationRules>`                         Get the currently active validation rules.

:ref:`getConfiguration() <developerManual-javaScript-field-getConfiguration>`                                               Fetch the configuration of a field.

:ref:`getElement() <developerManual-javaScript-field-getElement>`                                                           Fetch the DOM element of a field.

:ref:`getElements() <developerManual-javaScript-field-getElements>`                                                         Get the complete configuration.

:ref:`getFeedbackListContainer() <developerManual-javaScript-field-getFeedbackListContainer>`                               Fetch the DOM element of the validation feedback container.

:ref:`getErrors() <developerManual-javaScript-field-getErrors>`                                                             Get the errors.

:ref:`getFieldContainer() <developerManual-javaScript-field-getFieldContainer>`                                             Fetches the container of a field.

:ref:`getForm() <developerManual-javaScript-field-getForm>`                                                                 Get the parent form.

:ref:`getLastValidationErrorName() <developerManual-javaScript-field-getLastValidationErrorName>`                           Get the last validation error name.

:ref:`getMessageTemplate() <developerManual-javaScript-field-getMessageTemplate>`                                           Get the message template.

:ref:`getName() <developerManual-javaScript-field-getName>`                                                                 Get the name of a field.

:ref:`getValue() <developerManual-javaScript-field-getValue>`                                                               Get the value of a field.

:ref:`handleLoadingBehaviour() <developerManual-javaScript-field-handleLoadingBehaviour>`                                   Handle the loading behaviour.

:ref:`hasError() <developerManual-javaScript-field-hasError>`                                                               Check if there is an error.

:ref:`insertErrors() <developerManual-javaScript-field-insertErrors>`                                                       Insert errors.

:ref:`isValid() <developerManual-javaScript-field-isValid>`                                                                 Check if a field is valid.

:ref:`isValidating() <developerManual-javaScript-field-isValidating>`                                                       Check if the field validation process is currently running.

:ref:`onError() <developerManual-javaScript-field-onError>`                                                                 Hook at a validation error.

:ref:`onValidationBegins() <developerManual-javaScript-field-onValidationBegins>`                                           Hook at the validation beginning.

:ref:`onValidationDone() <developerManual-javaScript-field-onValidationDone>`                                               Hook at the validation end.

:ref:`refreshFeedback() <developerManual-javaScript-field-refreshFeedback>`                                                 Empty the feedback container.

:ref:`validate() <developerManual-javaScript-field-validate>`                                                               Launch the validation process.

:ref:`wasValidated() <developerManual-javaScript-field-wasValidated>`                                                       Check if the field was validated.
=========================================================================================================================== ====================================================================

.. _developerManual-javaScript-field-addActivationCondition:

Add an activation condition
---------------------------

.. container:: table-row

    Function
        ``addActivationCondition(name, callback)``
    Return
        /
    Parameters
        - ``name`` (String): arbitrary name of the activation condition.
        - ``callback`` (Function): the function which is called when the activation condition runs.
    Description
        Adds an activation condition for this field. It works like this: when JavaScript tries to validate a field, it will loop on all registered activation conditions for this field, and execute the function in ``callback``. If at least one of them returns ``false``, the field is considered as deactivated.

        The function ``callback`` has two parameters:

        - ``field``: the field instance which is currently validated;
        - ``continueValidation``: a function which **must** be called in your function, and contain **one parameter only**: a boolean which tells if the field is deactivated or not.

        .. warning::

            Be sure that ``continueValidation`` is called no matter what, it could lead to huge issues if you don't.

        .. attention::

            Note that if you work with JavaScript on situations where fields are (de)activated, you must probably do the same on the server side, in the :ref:`developerManual-php-formValidator`, thanks to the functions :ref:`deactivateField($fieldName) <formValidator-deactivateField>` and :ref:`activateField($fieldName) <formValidator-activateField>`.

        **Example:**

        .. code-block:: javascript

            form.getFieldByName('email').addActivationCondition(
                'customConditionEmail',
                function (field, continueValidation) {
                    var flag = true;

                    if (jQuery('#some-random-element').hasClass('test')) {
                        flag = false;
                    }

                    continueValidation(flag);
                }
            );

        .. hint::

            You can add as many activation conditions as you want.

        .. note::

            This function is used by Formz core, in code automatically generated from values written in the TypoScript configuration of the :ref:`fields activation conditions <fieldsActivation-condition>`.

.. _developerManual-javaScript-field-addActivationConditionForValidator:

Add an activation condition to a validation rule
------------------------------------------------

.. container:: table-row

    Function
        ``addActivationConditionForValidator(name, validationName, callback)``
    Return
        /
    Parameters
        - ``name`` (String): arbitrary name of the activation condition.
        - ``validationName`` (String): name of the validation rule onto which the activation condition is bound.
        - ``callback`` (Function): the function which is called when the activation condition runs.
    Description
        It's the same as above, but for a validation rule.

        For example, a field may have two validation rules, one of which can be deactivated depending on criteria of your own environment.

        .. attention::

            Note that if you work with JavaScript on situations where validation rules are (de)activated, you must probably do the same on the server side, in the :ref:`developerManual-php-formValidator`, thanks to the functions :ref:`deactivateFieldValidator($fieldName, $validatorName) <formValidator-deactivateFieldValidator>` and :ref:`activateFieldValidator($fieldName, $validatorName) <formValidator-activateFieldValidator>`.

        In the example below, we manipulate the rule ``required`` of the field ``email``. There can be a situations where this rule is deactivated, but the field will still have another rule ``isEmail`` which checks that the value is a correct email address, and is always activated.

        **Example:**

        .. code-block:: javascript

            form.getFieldByName('email').addActivationConditionForValidator(
                'customConditionEmailRequired',
                'required',
                function (field, continueValidation) {
                    var flag = true;

                    if (customFunctionToCheckIfFieldsAreRequired()) {
                        flag = false;
                    }

                    continueValidation(flag);
                }
            );

        .. hint::

            You can add as many activation conditions as you want.

        .. note::

            This function is used by Formz core, in code automatically generated from values written in the TypoScript configuration of the :ref:`validation rules activation conditions <validatorsActivation>`.

-----

.. _developerManual-javaScript-field-addValidation:

Add a validation rule
---------------------

.. container:: table-row

    Function
        ``addValidation(validationName, validatorName, validationConfiguration)``
    Return
        /
    Parameters
        - ``validationName`` (String): name of the rule (its index), must be unique for this field.
        - ``validatorName`` (String): name of the validator used for this rule. It must be an existing validator, registered with :ref:`Formz.Validation.registerValidator() <developerManual-javaScript-validation-registerValidator>`.
        - ``validationConfiguration`` (Object): configuration of the validator, which will be sent when validation begins.
    Description
        Adds a validation rule to the field.

        It's then possible to manipulate this rule with the function :ref:`addActivationConditionForValidator() <developerManual-javaScript-field-addActivationConditionForValidator>`.

-----

.. _developerManual-javaScript-field-checkActivationCondition:

Check a field activation
------------------------

.. container:: table-row

    Function
        ``checkActivationCondition(runValidationCallback, stopValidationCallback)``
    Return
        /
    Parameters
        - ``runValidationCallback`` (Function): function called if the field is activated.
        - ``stopValidationCallback`` (Function): function called if the field is deactivated.
    Description
        This function checks if a field is currently activated, or not.

        Depending if the field is activated or not, one of the two parameters functions is called.

        **Example:**

        .. code-block:: javascript

            form.getFieldByName('email').checkActivationCondition(
                function() {
                    alert('Field is activated!');
                },
                function() {
                    alert('Field is deactivated!');
                }
            );

-----

.. _developerManual-javaScript-field-checkActivationConditionForValidator:

Check a field validator activation
----------------------------------

.. container:: table-row

    Function
        ``checkActivationConditionForValidator(validatorName, runValidationCallback, stopValidationCallback)``
    Return
        /
    Parameters
        - ``validatorName`` (String): name of the wanted validator, for instance ``required``.
        - ``runValidationCallback`` (Function): function called if the validator is activated.
        - ``stopValidationCallback`` (Function) : function called if the validator is deactivated.
    Description
        This function checks if a given validator for the field is currently activated, or not.

        Depending if the validator is activated or not, one of the two parameters functions is called.

        **Example:**

        .. code-block:: javascript

            form.getFieldByName('email').checkActivationCondition(
                'required',
                function() {
                    alert('The field is required!');
                },
                function() {
                    alert('The field is not required!');
                }
            );

-----

.. _developerManual-javaScript-field-getActivatedValidationRules:

Get currently active validation rules
-------------------------------------

.. container:: table-row

    Function
        ``getActivatedValidationRules(callback)``
    Return
        /
    Parameters
        - ``callback`` (Function): function called when the validation rules list is built.
    Description
        Function allowing to fetch the list of currently active validation rules for the field.

        **Example:**

        .. code-block:: javascript

            form.getFieldByName('email').getActivatedValidationRules(
                function(activatedRules) {
                    if ('required' in activatedRules) {
                        // ...
                    }
                }
            );

-----

.. _developerManual-javaScript-field-getConfiguration:

Get the field configuration
---------------------------

.. container:: table-row

    Function
        ``getConfiguration()``
    Return
        ``Object``
    Description
        Returns the configuration object of the field, which may contain some useful data.

-----

.. _developerManual-javaScript-field-getElement:

Get the field DOM element
-------------------------

.. container:: table-row

    Function
        ``getElement()``
    Return
        ``Element``
    Description
        Returns the HTML element (in the DOM) of the field.

        .. attention::

            This function must be used only for fields that contain a unique element, for instance the fields of type ``select`` or ``text``.

            For fields containing several elements, like ``checkbox`` or ``radio``, use the function ``getElements()``.

-----

.. _developerManual-javaScript-field-getElements:

Get the field DOM elements
--------------------------

.. container:: table-row

    Function
        ``getElements()``
    Return
        ``NodeList``
    Description
        Returns the HTML elements (in the DOM) of the field.

        .. attention::

            This function must be used for fields that contain several elements, for instance the fields of type ``checkbox`` or ``radio``.

            For fields containing a unique element, like ``select`` or ``text``, use the function ``getElement()``.

-----

.. _developerManual-javaScript-field-getFeedbackListContainer:

Get the DOM element of the feedback list container
--------------------------------------------------

.. container:: table-row

    Function
        ``getFeedbackListContainer()``
    Return
        ``Element``
    Description
        The feedback container is a block which is automatically updated by JavaScript, which will insert the messages returned by the used validators.

        It's not recommended to interact directly with the content of this block, but you may proceed to other operations like add classes for instance.

        .. note::

            The value of the TypoScript parameter :ref:`settings.feedbackListSelector <fieldsSettings-feedbackListSelector>` is used to select the element.

-----

.. _developerManual-javaScript-field-getErrors:

Get the errors
--------------

.. container:: table-row

    Function
        ``getErrors()``
    Return
        ``Object``
    Description
        Returns the current field's errors.

        .. warning::

            Calling this function makes sense only when the field has been validated. Check the function :ref:`wasValidated() <developerManual-javaScript-field-wasValidated>` for further information.

-----

.. _developerManual-javaScript-field-getFieldContainer:

Get the field container
-----------------------

.. container:: table-row

    Function
        ``getFieldContainer()``
    Return
        ``Element``
    Description
        Returns the DOM element which contains the entire field template.

        .. note::

            The value of the TypoScript parameter :ref:`settings.fieldContainerSelector <fieldsSettings-fieldContainerSelector>` is used to select the element.

-----

.. _developerManual-javaScript-field-getForm:

Get the parent form
-------------------

.. container:: table-row

    Function
        ``getForm()``
    Return
        ``Formz.FullForm``
    Description
        Returns the form instance from which this field comes.

-----

.. _developerManual-javaScript-field-getLastValidationErrorName:

Get the last validation error name
----------------------------------

.. container:: table-row

    Function
        ``getLastValidationErrorName()``
    Return
        ``String``
    Description
        Returns the name of the last validation rule which returned an error during the field validation.

-----

.. _developerManual-javaScript-field-getMessageTemplate:

Get the message template
------------------------

.. container:: table-row

    Function
        ``getMessageTemplate()``
    Return
        ``String``
    Description
        Returns the message template which is used when a message is added in the :ref:`feedback list container <developerManual-javaScript-field-getFeedbackListContainer>`.

        This template can be fetched using two ways:

        1. With the TypoScript configuration :ref:`settings.messageTemplate <fieldsSettings-messageTemplate>` ;
        2. With the :ref:`HTML block <integratorManual-configuration-messageTemplate>`.

-----

.. _developerManual-javaScript-field-getName:

Get the field name
------------------

.. container:: table-row

    Function
        ``getName()``
    Return
        ``String``
    Description
        Returns the name of the field.

-----

.. _developerManual-javaScript-field-getValue:

Get the field value
-------------------

.. container:: table-row

    Function
        ``getValue()``
    Return
        ``String|Array``
    Description
        Returns the current value of the field.

        The return type may differ depending on the field type. If it is a “unique” field with one element (for instance ``select`` or ``text``), then the value of this field is returned. But for “multiple” fields (for instance ``checkbox`` or ``radio``), an array containing the selected values is returned.

-----

.. _developerManual-javaScript-field-handleLoadingBehaviour:

Handle the loading behaviour
----------------------------

.. container:: table-row

    Function
        ``handleLoadingBehaviour(run)``
    Return
        /
    Parameters
        - ``run`` (Boolean): ``true`` if the loading behaviour is triggered, otherwise ``false``.
    Description
        Activates or deactivates the loading behaviour of the field. A CSS class is added to the field container, which allows to easily add a loading effect, like an animated loading circle next to the field.

        By default, this behaviour is triggered when the field validation process begins, and is stopped when the validation is done.

        You may manipulate this behaviour as you like if you do heavy operations on your field, and you want to indicate to the user that the process is running (very useful for Ajax request for instance).

-----

.. _developerManual-javaScript-field-hasError:

Check if the field has an error
-------------------------------

.. container:: table-row

    Function
        ``hasError(validationName, errorName)``
    Return
        ``Boolean``
    Parameters
        - ``validationName`` (String): name of the validation rule which may contain an error.
        - ``errorName`` (String): name of the wanted error, usually ``default``. If the given value is ``null``, any error found for this validation rule will match.
    Description
        Checks if the field has a given error.

-----

.. _developerManual-javaScript-field-insertErrors:

Insert errors
-------------

.. container:: table-row

    Function
        ``insertErrors(errors)``
    Return
        /
    Parameters
        - ``errors`` (Object): list of errors to be inserted in the feedback container.
    Description
        Insert errors in the feedback container. The error list is an object, for which each key is the name of a validation rule, the second key is the name of the error and the value is the error message.

        **Example:**

        .. code-block:: javascript

            var errors = {customRule: {message: 'hello world!'}};
            form.getFieldByName('email').insertErrors(errors);

-----


.. _developerManual-javaScript-field-isValid:

Check if the field is valid
---------------------------

.. container:: table-row

    Function
        ``isValid()``
    Return
        ``Boolean``
    Description
        Checks if the field is valid.

        .. warning::

            Calling this function makes sense only when the field has been validated. Check the function :ref:`wasValidated() <developerManual-javaScript-field-wasValidated>` for further information.

-----

.. _developerManual-javaScript-field-isValidating:

Check if the field validation process is currently running
----------------------------------------------------------

.. container:: table-row

    Function
        ``isValidating()``
    Return
        ``Boolean``
    Description
        Returns ``true`` if the field validation process is currently running.

-----

.. _developerManual-javaScript-field-onError:

Spot a validation error
-----------------------

.. container:: table-row

    Function
        ``onError(validationName, errorName, callback)``
    Return
        /
    Parameters
        - ``validationName`` (String): name of the validation rule which returns an error.
        - ``errorName`` (String): name of the returned error, usually ``default``. If the given value is ``null``, any error found for this validation rule will trigger the event.
        - ``callback`` (Function): function called when the field validation encounters the error specified by ``validationName`` and ``errorName``.
    Description
        Allows connecting a function on the event triggered when a given error is found during the field validation.

-----

.. _developerManual-javaScript-field-onValidationBegins:

Spot the validation beginning
-----------------------------

.. container:: table-row

    Function
        ``onValidationBegins(callback)``
    Return
        /
    Parameters
        - ``callback`` (Function): function called when the field validation process begins.
    Description
        Allows hooking a function at the beginning of the field validation process.

-----

.. _developerManual-javaScript-field-onValidationDone:

Spot the validation ending
--------------------------

.. container:: table-row

    Function
        ``onValidationDone(callback)``
    Return
        /
    Parameters
        - ``callback`` (Function): function called when the field validation process ends.
    Description
        Allows hooking a function at the end of the field validation process.

-----

.. _developerManual-javaScript-field-refreshFeedback:

Empty feedback container
------------------------

.. container:: table-row

    Function
        ``refreshFeedback()``
    Return
        /
    Description
        Completely empty the feedback container. You may insert new messages with the function “:ref:`insertErrors(errors) <developerManual-javaScript-field-insertErrors>`”.

-----

.. _developerManual-javaScript-field-validate:

Launch the validation process
-----------------------------

.. container:: table-row

    Function
        ``validate()``
    Return
        /
    Description
        Launches the field validation process.

        It's possible to hook a function at the beginning of the validation process with the function :ref:`onValidationBegins() <developerManual-javaScript-field-onValidationBegins>`, and on the end of the validation process with the function :ref:`onValidationDone() <developerManual-javaScript-field-onValidationDone>`.

-----

.. _developerManual-javaScript-field-wasValidated:

Check if the field was validated
--------------------------------

.. container:: table-row

    Function
        ``wasValidated()``
    Return
        ``Boolean``
    Description
        Returns ``true`` if the field has finished at least once a validation process.

        This function is useful for other functions which depend on the result of the validation, like the function :ref:`isValid() <developerManual-javaScript-field-isValid>`.
