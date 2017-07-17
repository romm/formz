.. include:: ../Includes.txt

.. _usage:

Installation and usage
======================

Installation
^^^^^^^^^^^^

You can find a schema below to easily understand the proceedings of a form usage:

.. only:: html

    .. figure:: ../Images/schema-form-creation.svg
        :alt: Schema representing a form creation
        :figwidth: 70%

-----

Download
--------

To make FormZ work properly, you must install:

- **configuration_object** – this extension allows converting the FormZ TypoScript configuration. Without detailing too much, its principal feature is to detect errors in configuration. It's mandatory.

  You can install it:

  - With Composer : ``composer require romm/configuration-object:*``
  - With the TER : `Configuration Object <https://typo3.org/extensions/repository/view/configuration_object>`_

- **formz** – the hearth of the extension.

  You can install it:

  - With Composer : ``composer require romm/formz:*``
  - With the TER : `FormZ <https://typo3.org/extensions/repository/view/formz>`_

-----

Creating a new form
^^^^^^^^^^^^^^^^^^^

Here are the main steps for setting up a new form. Note that you can read the small guide which explains the creation of a full form here: “:ref:`tutorial`”.

.. tip::

    To understand quickly how it works, you can download an extension containing a form example in the chapter “:ref:`example`”.

The PHP side
------------

To initiate the creation of a new form, it is advised to begin with the development of the PHP architecture.

A form will be represented by a **data model** (see “:ref:`developerManual-php-model`”), and its display should be managed by a plug-in (or other), handled by a **controller**. Until then, it is a common way to deal with an extension based on Extbase.

The true usefulness of the extension comes with the usage of **form validators** and **field validators**. You may read the chapters “:ref:`developerManual-php-formValidator`” and “:ref:`developerManual-php-validator`”.

-----

Integration
-----------

The HTML integration of a form is similar to a classic Fluid integration, but some tools must be used, and some standards respected to insure the form proper functioning.

Check the chapters in “:ref:`integratorManual`“ to read more.

-----

Configuration
-------------

Once the displaying is handled, the validation rules can be managed with TypoScript configuration.

To learn how to configure the several settings, read the chapters in “:ref:`usersManual`”.

-----

You can find below an example of a tree view of the needed files for a form set up.

.. only:: html

    .. figure:: ../Images/files-tree.svg
        :alt: List of files for a form
        :figwidth: 300px

-----

“Debug” mode
^^^^^^^^^^^^

A “Debug” mode is available and allows, if activated, to get additional information when a problem occurs.

To activate it, go in the extension manager, inside the FormZ options, and check the option “**debugMode**”.

.. warning::

    It is **highly discouraged** to activate this mode in a **production environment**!
