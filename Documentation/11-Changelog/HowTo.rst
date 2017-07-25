.. include:: ../Includes.txt

.. _changelog-howTo:

Documenting changes
===================

FormZ is using a similar way as TYPO3 for documenting its changes. The official guide on how to contribute can be found here:

https://docs.typo3.org/typo3cms/extensions/core/latest/Changelog/Howto.html

Some rules differ though:

- The folder ``master`` is replaced by ``Development``.
- No forge issue number can be used, so the filename convention is set to ``<type>-<UpperCamelCaseDescription>.rst``
- Tagging changes is not mandatory.
- The folder containing a version and its changes must be prefixed by ``v`` (e.g. ``v2.3.4``).

.. _changelog-howTo-newRelease:

New release
-----------

For a new release, the steps below must be followed:

1. The folder ``Release`` should be duplicated to a new folder named after the new version number (prefixed by a ``v``);

2. Every changelog file in ``Development/Changes`` must be moved into the ``Changes`` sub-folder of the newly created folder;

3. The files ``Release.txt`` must be updated with correct information about the new release;

4. The file ``Notes.rst`` should contain general information about the new release.

.. note::

    You can find the current development branch changelog here: :ref:`changelog-development`.

-----

**Example**

For the release of the version ``1.42.0``:

**Before:**

.. code::

    |── Development
    |   |── Index.rst
    |   |── Changes
    |       |── Feature-SomeFeature.rst
    |       |── Deprecation-SomeDeprecationChange.rst
    |── Release
    |   |── Index.rst
    |   |── Notes.rst
    |   |── Release.txt

**After:**

.. code::

    |── Development
    |   |── Index.rst
    |── v1.42.0
    |   |── Index.rst
    |   |── Notes.rst
    |   |── Release.txt
    |   |── Changes
    |       |── Feature-SomeFeature.rst
    |       |── Deprecation-SomeDeprecationChange.rst
