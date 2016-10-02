.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-field:

Field
=====

Permet le paramétrage du rendu d'un champ du formulaire.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Le nom du champ.

                        Il doit s'agir d'une propriété accessible dans le :ref:`modèle du formulaire <developerManual-php-model>`.

\* ``layout``           Modèle utilisé pour ce champ. Pour plus d'informations, lire le chapitre « :ref:`integratorManual-layouts` ».

``arguments``           Liste d'arguments envoyés lors du rendu du layout du champ. Si possible, préférez utiliser le ViewHelper
                        :ref:`integratorManual-viewHelpers-option`, qui permet notamment une meilleure lecture du code.
======================= ================================================================================================================

Exemple
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 5,7

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:form action="submitForm" name="myForm">

        <formz:field name="email" layout="default">
            ...
        </formz:field>

    </formz:form>
