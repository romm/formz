.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-formatMessage:

FormatMessage
=============

Permet de formatter un message renvoyé par la validation d'un champ, en fonction du :ref:`fieldsSettings-messageTemplate` configuré pour ce champ.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``message``          Instance de message de validation. Logiquement, il s'agit d'une instance renvoyée par le ViewHelper de Fluid :
                        ``f:form.validationResults``.

``field``               Si pour une quelconque raison vous utilisez ce ViewHelper en dehors du ViewHelper
                        :ref:`integratorManual-viewHelpers-field`, vous pouvez remplir l'argument ``field`` avec le nom du champ pour
                        lequel vous souhaitez formatter le message.

                        Il doit s'agir d'un nom de champ valide pour le formulaire actuel.
======================= ================================================================================================================

Exemple
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 5

        {namespace formz=Romm\Formz\ViewHelpers}

        <f:form.validationResults for="{formName}.{fieldName}">
            <f:for each="{validationResults.errors}" iteration="iteration" as="error">
                <formz:formatMessage message="{error}" />
            </f:for>
        </f:form.validationResults>
