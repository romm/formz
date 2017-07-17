.. include:: ../../../Includes.txt

.. _integratorManual-viewHelpers:

ViewHelpers
===========

FormZ propose une panoplie de ViewHelpers pour faciliter l'intégration :

- :ref:`formz:form <integratorManual-viewHelpers-form>`

  Permet d'initialiser FormZ. Il remplace le ViewHelper ``form`` fournit par Extbase, et devra impérativement être utilisé.

- :ref:`formz:field <integratorManual-viewHelpers-field>`

  Lance le rendu d'un champ du formulaire, en automatisant un bon nombre de processus.

- :ref:`formz:option <integratorManual-viewHelpers-option>`

  Définit la valeur d'une option, qui pourra être utilisée dans le rendu d'un champ.

- :ref:`formz:slot <integratorManual-viewHelpers-slot>`

  Définit un slot dans le gabarit d'un champ.

- :ref:`formz:slot.render <integratorManual-viewHelpers-slot-render>`

  Lance le rendu d'un slot défini dans le gabarit d'un champ.

- :ref:`formz:slot.has <integratorManual-viewHelpers-slot-has>`

  Conditionne le rendu d'un bloc sur la présence d'un slot.

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
    SlotViewHelper
    Slot/RenderViewHelper
    Slot/HasViewHelper
    FormatMessageViewHelper
    ClassViewHelper
