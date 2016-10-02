.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationForms:

Formulaires
===========

La configuration des formulaires est accessible au chemin ``config.tx_formz.forms``. On y retrouve la liste de tous les formulaires. Chaque configuration doit porter comme clé le nom de la classe du modèle dudit formulaire.

Exemple : ``config.tx_formz.forms.MyVendor\MyExtension\Form\ExampleForm { ... }``

Propriétés
----------

Retrouvez ci-dessous la liste des paramètres utilisables par un formulaire.

=============================================================== ============================
Propriété                                                       Titre
=============================================================== ============================
\* :ref:`fields <formFields>`                                   Champs du formulaire

:ref:`activationCondition <formActivationCondition>`            Conditions d'activation

:ref:`settings.defaultClass <formDefaultClass>`                 Classe par défaut

:ref:`settings.defaultErrorMessage <formDefaultErrorMessage>`   Message d'erreur par défaut
=============================================================== ============================

-----

.. _formFields:

Champs du formulaire
--------------------

.. container:: table-row

    Propriété
        ``fields``
    Requis ?
        Oui
    Description
        Contient la liste des champs du formulaire.

        Notez que chaque champ doit correspondre à une propriété du modèle PHP du formulaire pour être prise en compte.

.. _formActivationCondition:

Conditions d'activation
-----------------------

.. container:: table-row

    Propriété
        ``activationCondition``
    Requis ?
        Non
    Description
        Contient la liste des conditions d'activation qui seront utilisables par tous les champs de ce formulaire. Ces différentes conditions pourront ensuite être utilisées dans les expressions logiques d'activation d'un champ (cf. « :ref:`fieldsActivation-condition` ») ou d'une validation de champ.

        Pour plus d'informations sur ce fonctionnement, consultez le chapitre « :ref:`usersManual-typoScript-configurationActivation` ».

        **Exemple :**

        .. code-block:: typoscript

            activationCondition {
                colorIsRed {
                    type = fieldHasValue
                    fieldName = color
                    fieldValue = red
                }

                colorIsBlue {
                    type = fieldHasValue
                    fieldName = color
                    fieldValue = blue
                }
            }

        .. note::

            Il existe plusieurs types de conditions disponibles dans le cœur de Formz, cf. le chapitre « :ref:`usersManual-typoScript-configurationActivation` ».

.. _formDefaultClass:

Classe par défaut
-----------------

.. container:: table-row

    Propriété
        ``settings.defaultClass``
    Requis ?
        Non
    Description
        Classe qui sera donnée par défaut à la balise ``<form>`` lors de l'utilisation du ViewHelper :php:`Romm\Formz\ViewHelpers\FormViewHelper`.

        La valeur par défaut est ``formz``.

.. _formDefaultErrorMessage:

Message d'erreur par défaut
---------------------------

.. container:: table-row

    Propriété
        ``settings.defaultErrorMessage``
    Requis ?
        Non
    Description
        Lorsqu'une erreur est attribuée à un champ, si pour une raison inconnue le message d'erreur retourné est vide, le message indiqué dans cette propriété sera utilisé.

        Peut contenir une référence LLL.

