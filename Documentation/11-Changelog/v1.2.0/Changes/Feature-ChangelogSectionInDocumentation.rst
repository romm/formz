.. include:: ../../../Includes.txt

.. _1-2-0-feature-changelogSectionDocumentation:

===========================================
Feature: Changelog section in documentation
===========================================

Description
===========

FormZ now offers a reliable way of documenting its changes from one version to another. The goal is to have a detailed note for each important change among the following list:

* **Feature**: new functionality added to FormZ;
* **Breaking**: some part of the core has been changed, and may break external extension;
* **Deprecation**: a functionality is marked as old and not maintained anymore, and will be removed in a future release;
* **Important**: any other important change.

The concept already exists in TYPO3 core (see `official documentation <https://docs.typo3.org/typo3cms/extensions/core/8.7/Changelog/Howto.html>`_), and the main principles remain the same.

From now on, before upgrading to a new FormZ version, one should first read the changelog and easily check for changes that may have impacts on their own implementation.

Impact
======

Every important change must now be properly documented, by :ref:`following the rules <changelog-howTo>` that explain how to do it properly.

Before a new release, every changelog file will be placed into a new folder corresponding to the new version number.
