.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _developerManual-javaScript-form:

Form
====

Below you can find a list of the available functions with a **form instance**:

=============================================================================== ==========================================================
Function                                                                        Description
=============================================================================== ==========================================================
:ref:`getConfiguration() <developerManual-javaScript-form-getConfiguration>`    Returns the complete configuration.

:ref:`getElement() <developerManual-javaScript-form-getElement>`                Returns the form DOM element.

:ref:`getFieldByName() <developerManual-javaScript-form-getFieldByName>`        Fetches a given field.

:ref:`getFields() <developerManual-javaScript-form-getFields>`                  Fetches all the fields of this form.

:ref:`getName() <developerManual-javaScript-form-getName>`                      Returns the name of the form.

:ref:`onSubmit() <developerManual-javaScript-form-onSubmit>`                    Binds a function on the form submission.
=============================================================================== ==========================================================

.. _developerManual-javaScript-form-getConfiguration:

Fetch the configuration
-----------------------

.. container:: table-row

    Function
        ``getConfiguration()``
    Return
        ``Array``
    Description
        Fetches the whole form configuration. It is mostly the TypoScript configuration, so you have access to some values which were previously set in TypoScript.

        **Example:**

        .. code-block:: javascript

            var formConfiguration = form.getConfiguration();
            var message = formConfiguration['settings']['defaultErrorMessage'];

-----

.. _developerManual-javaScript-form-getElement:

Get the DOM element
-------------------

.. container:: table-row

    Function
        ``getElement()``
    Return
        ``HTMLFormElement``
    Description
        Returns the DOM element of the form. You may then manipulate it.

        **Example:**

        .. code-block:: javascript

            var formElement = form.getElement();
            formElement.classList.add('some-class');

-----

.. _developerManual-javaScript-form-getFieldByName:

Get a given field
-----------------

.. container:: table-row

    Function
        ``getFieldByName(name)``
    Return
        ``Fz.FullField``
    Parameters
        - ``name``: name of the field.
    Description
        Returns a given field, which you can then manipulate.

        **Example:**

        .. code-block:: javascript

            var fieldEmail = form.getFieldByName('email');

-----

.. _developerManual-javaScript-form-getFields:

Get all fields
--------------

.. container:: table-row

    Function
        ``getFields()``
    Return
        ``Object<Fz.FullField>``
    Description
        Returns all the fields of this form.

        **Example:**

        .. code-block:: javascript

            var fields = form.getFields();
            for (var fieldName in fields) {
                // ...
            }

-----

.. _developerManual-javaScript-form-getName:

Get the name of the form
------------------------

.. container:: table-row

    Function
        ``getName()``
    Return
        ``String``
    Description
        Returns the name of the form.

        **Example:**

        .. code-block:: javascript

            var message = 'The form ' + form.getName() + ' has been submitted.';

-----

.. _developerManual-javaScript-form-onSubmit:

Bind a function on the form submission
--------------------------------------

.. container:: table-row

    Function
        ``onSubmit(callback)``
    Return
        /
    Parameters
        - ``callback``: function called when the form is submitted. If it returns false, the form submission is cancelled.
    Description
        Binds a function on the form submission. Note that the function wont be called if the form submission is blocked (for instance because of an invalid field).

        The function can return ``false`` if the submission must be blocked for any reason.

        **Example:**

        .. code-block:: javascript

            form.onSubmit(function() {
                var foo = bar();
                if (true === foo) {
                    return false;
                }
            });
