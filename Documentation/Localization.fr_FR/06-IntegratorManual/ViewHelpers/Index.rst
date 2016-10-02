.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers:

ViewHelpers
===========

Formz propose une panoplie de ViewHelpers pour faciliter l'intégration :

- :ref:`formz:form <integratorManual-viewHelpers-form>`

  Permet d'initialiser Formz. Il remplace le ViewHelper ``form`` fournit par Extbase, et devra impérativement être utilisé.

- :ref:`formz:field <integratorManual-viewHelpers-field>`

  Lance le rendu d'un champ du formulaire, en automatisant un bon nombre de processus.

- :ref:`formz:option <integratorManual-viewHelpers-option>`

  Définit la valeur d'une option, qui pourra être utilisée dans le rendu d'un champ.

- :ref:`formz:section <integratorManual-viewHelpers-section>`

  Définit une section dans le gabarit d'un champ.

- :ref:`formz:renderSection <integratorManual-viewHelpers-renderSection>`

  Lance le rendu d'une section définie dans le gabarit d'un champ.

- :ref:`formz:formatMessage <integratorManual-viewHelpers-formatMessage>`

  Permet de formatter un message renvoyé par la validation d'un champ.

- :ref:`formz:class <integratorManual-viewHelpers-class>`

  Gère dynamiquement les classes CSS en fonction de la validité d'un champ : active ou désactive certaines classes CSS selon le résultat de la validation du champ.

.. toctree::
    :maxdepth: 5
    :titlesonly:
    :hidden:

    FormViewHelper
    FieldViewHelper
    OptionViewHelper
    SectionViewHelper
    RenderSectionViewHelper
    FormatMessageViewHelper
    ClassViewHelper
