.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _developerManual-php-validator:

Validator
=========

Validators are used to check the values of the fields submitted with a form. Their behaviour is almost similar to the base validators from TYPO3, but they do have **some more functionality**.

To configure the usable validators in the forms configuration, read the chapter “:ref:`usersManual-typoScript-configurationValidators`”.

You have the possibility to create your own validators depending on your needs; make sure that they inherit ``Romm\Formz\Validation\Validator\AbstractValidator``, and use correctly the functions from the API.

API
^^^

The validators of Formz give you access to the following variables/functions:

- :ref:`$form <validator-form>`
- :ref:`$fieldName <validator-fieldName>`
- :ref:`$supportedMessages <validator-supportedMessages>`
- :ref:`$supportsAllMessages <validator-supportsAllMessages>`
- :ref:`$javaScriptValidationFiles <validator-javaScriptValidationFiles>`
- :ref:`addError($key, $code, array $arguments) <validator-addError>`
- :ref:`setValidationData($validationData) <validator-setValidationData>`
- :ref:`setValidationDataValue($key, $value) <validator-setValidationDataValue>`

-----

.. _validator-form:

Form instance
-------------

.. container:: table-row

    Property
        .. code-block:: php

            protected $form;
    Type
        :php:`Romm\Formz\Form\FormInterface`
    Description
        You have access with ``$this->form`` to **the submitted form instance**. It allows you for instance to apply some rules depending on the values of other fields.

        .. warning::

            ``$this->form`` is **read-only** accessible, you can't edit the fields values.

.. _validator-fieldName:

Field name
----------

.. container:: table-row

    Property
        .. code-block:: php

            protected $fieldName;
    Type
        :php:`string`
    Description
        Contains the name of the field currently validated by this validator.

.. _validator-supportedMessages:

Supported messages list
-----------------------

.. container:: table-row

    Property
        .. code-block:: php

            protected $supportedMessages = [];
    Type
        :php:`array`
    Description
        In Formz, validators use **pre-configured messages**. Indeed, a validator may return different messages; it should then define in advance what messages can be used: a key for the message, and its configuration.

        Use the variable ``$supportedMessages`` to define the list of messages used by the validator. You can check the following example to respect the structure:

        The values of these messages can be overridden by the fields TypoScript configuration.

        .. code-block:: php

            protected $supportedMessages = [
               // "default" is the message index.
               'default'    => [
                  // "key" is the LLL key of the message.
                  'key'        => 'validator.form.contains_values.error',

                  // "extension" contains the name of the extension used to
                  // fetch the LLL key of the message.
                  // If empty, "Formz" extension is used.
                  'extension'    => null
               ],
               'test'    => [
                  // If you fill "value", the value will be directly used and
                  // the process wont try to fetch a translation.
                  'value'        => 'Test message!'
               ]
            ];

.. _validator-supportsAllMessages:

Supports all messages
---------------------

.. container:: table-row

    Property
        .. code-block:: php

            protected $supportsAllMessages = false;
    Type
        :php:`bool`
    Description
        If a validator needs to be able to dynamically add error messages (for instance when using a web service), you can set this value to ``true``. You should set it to ``false`` by default, if you are not certain if you need it.

.. _validator-addError:

Add an error
------------

.. container:: table-row

    Function
        .. code-block:: php

            $this->addError($key, $code, array $arguments);
    Return
        /
    Parameters
        - ``$key``: the key of the message, must be an index of the array ``$supportedMessages``.
        - ``$code``: the code of the error, by convention it's the actual timestamp when the developer adds the error.
        - ``$arguments``: eventual arguments which will be replaced in the text of the message.
    Description
        You must use this function in order to add an error if the value does not pass the validation.

.. _validator-setValidationData:

Save information in an array
----------------------------

.. container:: table-row

    Function
        .. code-block:: php

            $this->setValidationData(array $validationData);
    Return
        /
    Parameters
        - ``$validationData``: arbitrary data array to be saved.
    Description
        When a validator is used on a form field, you may want to put some arbitrary information aside for a future usage. It is a plain array which can contain any information. This array will then be injected inside the form instance (``$this->form``) at the end of the validation process.

.. _validator-setValidationDataValue:

Save information value in an array
----------------------------------

.. container:: table-row

    Function
        .. code-block:: php

            $this->setValidationDataValue($key, $value);
    Return
        /
    Parameters
        - ``$key``: key of the arbitrary data to be saved.
        - ``$value``: arbitrary data to be saved.
    Description
        Same as above, but for a simple entry in the array.

.. _validator-javaScriptValidationFiles:

Bind a JavaScript file
----------------------

.. container:: table-row

    Property
        .. code-block:: php

            protected static $javaScriptValidationFiles = [];
    Type
        :php:`array`
    Description
        Contains the JavaScript files list which will emulate this validator in the client web browser. Just fill this array, Formz will import the files automatically.

        These files will have to contain the registration declaration of the validator JavaScript version, by using the function :ref:`Formz.Validation.registerValidator() <developerManual-javaScript-validation-registerValidator>`.

        **Example:**

        .. code-block:: php

            protected static $javaScriptValidationFiles = [
                'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.Required.js'
            ];

-----

Validator example
^^^^^^^^^^^^^^^^^

You can find below a validator example.

.. code-block:: php

    <?php
    namespace Romm\Formz\Validation\Validator;

    use Romm\Formz\Validation\Validator\AbstractValidator;

    class ContainsValuesValidator extends AbstractValidator {
        /**
         * @inheritdoc
         */
        protected $supportedOptions = [
           'values' => [
              [],
              'The values that are accepted',
              'array',
              true
           ]
        ];

        /**
         * @inheritdoc
         */
        protected $supportedMessages = [
           'default'    => [
              'key'        => 'validator.form.contains_values.error',
              'extension'    => null
           ]
        ];

        /**
         * @inheritdoc
         */
        public function isValid($valuesArray)
        {
           $flag = false;

           if (is_array($valuesArray)) {
              foreach ($valuesArray as $value) {
                 if (in_array($value, $this->options['values'])) {
                    $flag = true;
                    break;
                 }
              }
           }

           if (false === $flag) {
              $this->addError(
                 'default'
                 1445952458,
                 [implode(
                   ', ',
                   $this->options['values']
                )]
              );
           }
        }
    }
