.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _developerManual-javaScript:

API — JavaScript
=================

To allow JavaScript more advanced behaviours than those available by default with FormZ, you can use this API.

To fetch a form, you have to use a code similar to this one:

.. code-block:: javascript

    Fz.Form.get('myFormName', function(form) {
        // Whatever...
    });

The name of the form must match the name you gave to the ``ViewHelper`` in your template (see “:ref:`integratorManual-viewHelpers-form`”). You then have access to the variable ``form``, which allows you to manipulate whatever you need.

You can then manipulate:

* :ref:`The form <developerManual-javaScript-form>`
* :ref:`The form fields <developerManual-javaScript-field>`
* :ref:`The validation rules <developerManual-javaScript-validation>`

.. toctree::
    :maxdepth: 5
    :titlesonly:
    :hidden:

    Form
    Field
    Validation
