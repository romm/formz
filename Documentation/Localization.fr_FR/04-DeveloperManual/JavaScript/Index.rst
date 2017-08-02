.. include:: ../../../Includes.txt

.. _developerManual-javaScript:

API — JavaScript
=================

Pour permettre des comportements JavaScript plus évolués que ceux proposés par défaut par FormZ, une API est à disposition.

Pour récupérer un formulaire, vous devrez utiliser un code similaire à celui-ci :

.. code-block:: javascript

    Fz.Form.get('myFormName', function(form) {
        // Whatever...
    });

Le nom du formulaire correspond au nom que vous avez donné au ``ViewHelper`` dans votre template (cf. « :ref:`integratorManual-viewHelpers-form` »). Vous avez ensuite accès à la variable ``form``, qui vous permettra de manipuler ce que vous souhaitez.

Vous pouvez alors manipuler :

* :ref:`Le formulaire <developerManual-javaScript-form>`
* :ref:`Les champs du formulaire <developerManual-javaScript-field>`
* :ref:`Les règles de validation <developerManual-javaScript-validation>`

.. toctree::
    :maxdepth: 5
    :titlesonly:
    :hidden:

    Form
    Field
    Validation
