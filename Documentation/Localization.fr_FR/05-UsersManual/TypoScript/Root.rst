.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _developerManual-typoScript-root:

Racine de la configuration
==========================

================================================================================ ===========================================
Propriété                                                                        Titre
================================================================================ ===========================================
:ref:`forms <formzForms>`                                                        Liste des formulaires

:ref:`settings.defaultBackendCache <settingsDefaultBackendCache>`                Type de cache

:ref:`settings.defaultFormSettings <settingsDefaultFormSettings>`                Configuration par défaut des formulaires

:ref:`settings.defaultFieldSettings <settingsDefaultFieldSettings>`              Configuration par défaut des champs
================================================================================ ===========================================

.. _formzForms:

Liste des formulaires
---------------------

.. container:: table-row

    Propriété
        ``forms``
    Requis ?
        Non
    Description
        Contient la liste des formulaires qui utilisent Formz.

        Plus d'informations ici : « :ref:`usersManual-typoScript-configurationForms` ».


.. _settingsDefaultBackendCache:

Type de cache
-------------

.. container:: table-row

    Propriété
        ``settings.defaultBackendCache``
    Requis ?
        Oui
    Description
        Contient le type de cache utilisé par Formz.

        La valeur par défaut est :php:`TYPO3\CMS\Core\Cache\Backend\FileBackend`.

        Vous pouvez changer cette valeur selon vos besoins : il doit s'agir d'un type de cache backend valide (cf. https://docs.typo3.org/typo3cms/CoreApiReference/CachingFramework/FrontendsBackends/Index.html#cache-backends).

.. _settingsDefaultFormSettings:

Configuration par défaut des formulaires
----------------------------------------

.. container:: table-row

    Propriété
        ``settings.defaultFormSettings``
    Requis ?
        Oui
    Description
        Contient la configuration par défaut utilisée par les formulaires.

        Les propriétés que vous pouvez renseigner sont strictement les mêmes que les propriétés ``settings.*`` dans le chapitre « :ref:`usersManual-typoScript-configurationForms` ».

        Notez qu'une configuration de formulaire renseignée dans la propriété ``settings`` dudit formulaire écrase celle par défaut.

.. _settingsDefaultFieldSettings:

Configuration par défaut des champs
-----------------------------------

.. container:: table-row

    Propriété
        ``settings.defaultFieldSettings``
    Requis ?
        Oui
    Description
        Contient la configuration par défaut utilisée par les champs de formulaires.

        Les propriétés que vous pouvez renseigner sont strictement les mêmes que les propriétés ``settings.*`` dans le chapitre « :ref:`usersManual-typoScript-configurationFields` ».

        Notez qu'une configuration de champ renseignée dans la propriété ``settings`` dudit champ écrase celle par défaut.
