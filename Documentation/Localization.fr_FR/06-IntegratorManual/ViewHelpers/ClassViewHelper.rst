.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers-class:

Class
=====

Ce ViewHelper gère les classes dynamiques définies en TypoScript (cf. le chapitre « :ref:`viewClasses` »).

Le fonctionnement est le suivant : vous utilisez ce ViewHelper **pour un champ du formulaire**, pour initialiser **une classe CSS** faisant **partie d'une catégorie** (``valid`` ou ``errors``). Cette classe ne sera activée que si le champ fait partie de la catégorie.

Référez-vous à l'exemple plus bas pour comprendre plus facilement le fonctionnement.

.. note::

    Le comportement est géré à la fois en PHP et en JavaScript. Vous n'avez qu'à utiliser ce ViewHelper, FormZ se charge du reste.

Arguments
---------

======================= ================================================================================================================
Argument                Description
======================= ================================================================================================================
\* ``name``             Nom de la classe. Doit être composée du groupe de la classe (``valid`` ou ``errors``) et du nom réel de la
                        classe, séparés par un point. Exemple : ``valid.has-success``.

                        Il doit s'agir d'une classe définie en TypoScript (cf. le chapitre « :ref:`viewClasses` »).

``field``               Si pour une quelconque raison vous utilisez ce ViewHelper en dehors du ViewHelper
                        :ref:`integratorManual-viewHelpers-field`, vous pouvez remplir l'argument ``field`` avec le nom du champ pour
                        lequel vous souhaitez lier la classe.

                        Il doit s'agir d'un nom de champ valide pour le formulaire actuel.
======================= ================================================================================================================

Exemple
-------

On veut que le champ ``email`` ait la classe ``has-success`` s'il a passé toutes ses règles de validation.

Il faut commencer par définir la classe en TypoScript :

.. code-block:: typoscript
    :linenos:
    :emphasize-lines: 2-4

    config.tx_formz.view.classes {
        valid {
            has-success = has-success
        }
    }

On est ensuite en mesure de l'utiliser dans le template :

.. code-block:: html
    :linenos:
    :emphasize-lines: 7

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:form action="submitForm" name="myForm">

        <formz:field name="email" layout="default">
            <f:form.textfield property="{fieldName}" id="{fieldId}"
                              class="{formz:class(name: 'valid.has-success')}"
                              placeholder="Email" />
        </formz:field>

    </formz:form>
