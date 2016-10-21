.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

Introduction
============

Formz
^^^^^

*Check out Formz official website at: http://typo3-formz.com/*

Forms are **common elements in the conception of a website**, as they allow a **direct interaction** between the user and the application. Technically, setting up a form can quickly become **complex** and require a **lot of time**: many aspects must be considered: **style, displaying, validation, security**…

This is why Formz was born: facilitate the **set up** and the **maintenance** of a form, by providing tools which are **simple and fast to use**, but also **powerful and flexible** to fulfill every need.

Formz helps with the following topics:

- **HTML** – tools are provided for Fluid, to facilitate integration.
- **Validation** – with a TypoScript based configuration, every field's validation rule is easy to set up and maintain.
- **Style** – with its advanced “data attributes” system, Formz can fulfill almost all possible display needs.
- **UX** – a whole JavaScript API is provided to make a user experience as fast and as pleasant as possible.
- **Code generation** – Formz can generate JavaScript and CSS, which are then injected in the page and will automatize a part of the client-sided behaviours.

What is this for?
-----------------

The goal of Formz it to accelerate forms development, from a simple contact form to a complex subscription form. The extension provides a set of tools: developers, integrators and administrators will have access to ready-to-run and simple features.

A form manipulation can be divided into three principal axes: its **composition**, its **validation on submission**, and its **data exploitation** when it is validated. The last part is specific to each form, while composition and validation will always have similarities between forms: identical fields with same validation rules, same display, etc.

Indeed, it is common that fields are used several times in **different forms** of **a same website** (email address, phone number, etc.). In this case, it would be inconvenient to handle the configuration of these fields as many times as there are forms. To ease the administration, the extension is based on a **TypoScript configuration** to handle the validation rules of each form.

-----

How does it work?
-----------------

The first step is the set up of the form skeleton. As with any form built with Extbase, you will need at least a **controller**, a **model**, containing the data of the form, and the **Fluid architecture** (layouts, templates) which handles the form display.

Once this skeleton is set up, Formz will plug itself: some classes have to be used, some rules have to be respected, and the form's TypoScript configuration must be written.

When everything is correctly plugged in, the extension will **automatically** handle the fields and errors display, then proceed to the complete validation of the form. You won't have anything else to do.

-----

TL;DR
-----

“Formz” is an extension to help with forms manipulation in TYPO3. It has several goals:

- **Easy set up**

  Setting up a new working form is very **simple** and **fast**.

- **Easy maintenance**

  Thanks to a configuration **almost exclusively based on TypoScript**, it's very simple to maintain and update a form.

- **Resources gathering**

  The extension will automatize a lot of behaviours. It will particularly **generate CSS and JavaScript code** and inject them directly in the page.

-----

Example
-------

.. only:: html

    Live example from chapter “:ref:`example`”:

    .. figure:: ../Images/formz-example.gif
        :alt: Formz example

.. only:: latex

    See chapter “:ref:`example`”.

Going further
^^^^^^^^^^^^^

Although it is easy to set up, Formz remains a **powerful and modular** tool. It allows an easy overriding of its functionality, granting a proper adjustment to every form. Indeed, a form can quickly reach a level where automation is not enough, and specific developments are then required.

Formz provides an API to answer all of these needs: it is possible to override it in PHP and JavaScript to manipulate the form according to your needs; you will have access to all the needed information in this documentation.

.. only:: html

    You can stay in the presentation topic and learn more on the following topics:

.. toctree::
    :maxdepth: 5
    :titlesonly:

    TypoScriptConfiguration
    FieldsActivation
