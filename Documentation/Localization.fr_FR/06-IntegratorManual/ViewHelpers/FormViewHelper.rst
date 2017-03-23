.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-form:

Form
====

Remplace le ViewHelper de formulaire classique de Fluid (``<f:form>``).

Il fonctionne de la même manière que le ViewHelper classique, à une différence près : l'argument ``name`` est obligatoire (cf. ci-dessous).

.. important::

    Vous **devez impérativement** utiliser ce ViewHelper lors de l'intégration d'un formulaire qui utilise FormZ.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Doit correspondre de manière exacte au nom de la variable utilisée par l'action du contrôleur vers lequel le
                        formulaire pointe (cf. exemple plus bas).

\…                      Vous pouvez utiliser tous les arguments disponibles avec le ViewHelper de formulaire classique de Fluid.
======================= ================================================================================================================

Exemple
-------

.. code-block:: html

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form action="submitForm" name="myForm">
        ...
    </fz:form>

Le formulaire ci-dessus pointe vers l'action ``submitForm``. Si on va voir l'action dans la classe PHP du contrôleur :

.. code-block:: php

    public function submitFormAction(MyForm $myForm)
    {
        // ...
    }

Le nom de la variable est ``$myForm`` : le paramètre ``name`` du ViewHelper devra être identique.

Pour le reste, rien ne change, vous pouvez utiliser toutes les fonctionnalités d'un formulaire Fluid classique.
