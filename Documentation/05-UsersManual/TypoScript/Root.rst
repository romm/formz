.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _developerManual-typoScript-root:

Configuration root
==================

================================================================================ ===========================================
Property                                                                         Title
================================================================================ ===========================================
:ref:`forms <formzForms>`                                                        Forms list

:ref:`settings.defaultBackendCache <settingsDefaultBackendCache>`                Type of cache

:ref:`settings.defaultFormSettings <settingsDefaultFormSettings>`                Default forms configuration

:ref:`settings.defaultFieldSettings <settingsDefaultFieldSettings>`              Default fields configuration
================================================================================ ===========================================

.. _formzForms:

Forms list
----------

.. container:: table-row

    Property
        ``forms``
    Required?
        No
    Description
        Contains the list of forms using FormZ.

        More information here: “:ref:`usersManual-typoScript-configurationForms`”.


.. _settingsDefaultBackendCache:

Type of cache
-------------

.. container:: table-row

    Property
        ``settings.defaultBackendCache``
    Required?
        Yes
    Description
        Contains the type of cache used by FormZ.


        The default value is :php:`TYPO3\CMS\Core\Cache\Backend\FileBackend`.

        You may change this value to suit your needs: it must be a valid backend cache type (see https://docs.typo3.org/typo3cms/CoreApiReference/CachingFramework/FrontendsBackends/Index.html#cache-backends).

.. _settingsDefaultFormSettings:

Default forms configuration
---------------------------

.. container:: table-row

    Property
        ``settings.defaultFormSettings``
    Required?
        Yes
    Description
        Contains the default configuration used by forms.

        The properties which you can set here are strictly the same than the properties ``settings.*`` in the chapter “:ref:`usersManual-typoScript-configurationForms`”.

        Note that a form configuration set in the ``settings`` property of this form will override the default one.

.. _settingsDefaultFieldSettings:

Fields default configuration
----------------------------

.. container:: table-row

    Property
        ``settings.defaultFieldSettings``
    Required?
        Yes
    Description
        Contains the default configuration used by forms fields.

        The properties which you can set here are strictly the same as the properties ``settings.*`` in the chapter “:ref:`usersManual-typoScript-configurationFields`”.

        Note that a field configuration set in the ``settings`` property of this field will override the default one.
