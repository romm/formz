.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-option:

Option
======

Permet de définir la valeur d'un argument qui sera envoyé lors du rendu d'un champ.

Il s'agit d'une manière supplémentaire d'utiliser ``arguments`` du ViewHelper « :ref:`integratorManual-viewHelpers-field` ». Vous pouvez tout de même utiliser les deux fonctionnalités, mais le ViewHelper ``option`` écrasera un argument définit précédemment.

.. important::

    Ce ViewHelper doit impérativement être utilisé à l'intérieur d'un ViewHelper « :ref:`integratorManual-viewHelpers-field` ».

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Le nom de l'argument.

\* ``value``            La valeur de l'argument (peut être de n'importe quel type).
======================= ================================================================================================================

Exemple
-------

.. code-block:: html
    :linenos:
    :emphasize-lines: 6

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form action="submitForm" name="myForm">

        <fz:field name="email" layout="default">
            <fz:option name="required" value="1">

            ...
        </fz:field>

    </fz:form>
