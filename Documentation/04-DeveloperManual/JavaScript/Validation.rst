.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------

.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _developerManual-javaScript-validation:

Validation
==========

======================================================================================================== ==========================================================
Function                                                                                                 Description
======================================================================================================== ==========================================================
:ref:`Fz.Validation.registerValidator() <developerManual-javaScript-validation-registerValidator>`       Registers a new validator.
======================================================================================================== ==========================================================

.. _developerManual-javaScript-validation-registerValidator:

Register a validator
--------------------

.. container:: table-row

    Function
        ``Fz.Validation.registerValidator(name, callback)``
    Return
        /
    Parameters
        - ``name``: name of the validator, must be unique. If the validator is a JavaScript implementation of a PHP validator, then ``name`` must be the name of the validator PHP class, for instance ``Romm\Formz\Validation\Validator\RequiredValidator``.
        - ``callback``: the function which is called when the validator is used to know if a value is valid.
    Description
        Like their PHP implementation, validators allow JavaScript to know if a value is valid or not. The goal of JavaScript validators is to **instantly give to the user the indication on the validity of the given value for a field**, without having to wait for a server-side validation.

        Most of the time, a JavaScript validator is a **JavaScript conversion of the PHP validator algorithm**.

        The validator logic will be inside ``callback``, which follows these rules:

        1. It has three arguments:

           - ``value``: the value which must be validated.
           - ``callback``: the function which **must be called** when the validator finishes its validation work.
           - ``states``: an object containing the properties below:

             - ``result``: the result instance which is used to add errors if the validation does not pass.
             - ``configuration``: the configuration array of the validator.
             - ``data``: a list of properties which may be used during the validation.
             - ``validatorName``: name of the used validator.

        2. The validator must **in every case** call ``callback();``, because this is the way FormZ detects the end of the validation process. **Not calling it may break the entire FormZ process.**

        3. To add an error, you must use the result instance in ``states['result']``, see the example below.

        **Validator example:**

        .. code-block:: javascript

             Fz.Validation.registerValidator(
                 'Vendor\\Extension\\Validation\\Validator\\MyCustomValidator',
                 function (value, callback, states) {
                     if (value !== 'foo') {
                         states['result'].addError('default', states['configuration']['messages']['default']);
                     }

                     callback();
                 }
             );
