.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _usersManual-typoScript-configurationView:

View
====

To be able to correctly use all features during forms HTML integration, you will have to use some TypoScript parameters.

Properties
----------

=============================================================== =====================================
Property                                                        Title
=============================================================== =====================================
:ref:`classes <viewClasses>`                                    Dynamic HTML classes list.

:ref:`layouts <viewLayouts>`                                    Usable layouts list.

:ref:`layoutRootPaths <viewLayoutRootPaths>`                    Valid paths for layouts.

:ref:`partialRootPaths <viewPartialRootPaths>`                  Valid paths for partials.
=============================================================== =====================================

-----

.. _viewClasses:

Dynamic classes
---------------

.. container:: table-row

    Property
        ``classes``
    Required?
        No
    Description
        Contains the list of usable classes for the ViewHelper “:ref:`integratorManual-viewHelpers-class`”.

        The first level of this configuration must be a key among these two:

        * ``valid``: contains all classes which are activated when the field is **valid**.

        * ``errors``: contains all classes which are activated when the field is **not valid**.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.view {
                valid {
                    has-success = has-success
                }
                errors {
                    has-error = has-error
                }
            }

-----

.. _viewLayouts:

Layouts
-------

.. container:: table-row

    Property
        ``layouts``
    Required?
        No
    Description
        Contains the list of usable layouts for the property ``layout`` of the ViewHelper “:ref:`integratorManual-viewHelpers-field`”.

        Layouts are divided in groups, then in a list of layouts for each one of these groups.

        In each group, two properties must be filled:

        * ``templateFile``: path to the template which will be used for every layout of this group.

        * ``items``: list of layouts for this group: the key of every layout will be its identifier.

          + ``templateFile``: path to the template which will be used for this layout (overrides the value of the default one).

          + ``layout``: relative path to the layout (must be available in the paths defined in the property “:ref:`layoutRootPaths <viewLayoutRootPaths>`”).

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.view.layouts {
                application1 {
                    templateFile = EXT:extension/Resources/Private/Templates/Application1/Default.html
                    items {
                        one-column.layout = Application1/OneColumn
                        two-columns.layout = Application1/TwoColumns
                    }
                }

                application2 {
                    templateFile = EXT:extension/Resources/Private/Templates/Application2/Default.html
                    items {
                        one-column.layout = Application2/OneColumn

                        very-special-layout {
                            templateFile = EXT:extension/Resources/Private/Templates/Application2/Special.html
                            layout = Application2/TwoColumns
                        }
                    }
                }
            }

        .. note::

            Formz offers by default layouts for the CSS frameworks **Twitter Bootstrap** and **Foundation**. Read the chapter “:ref:`@todo <>`” for more information.

.. _viewLayoutRootPaths:

Layout root paths
-----------------

.. container:: table-row

    Property
        ``layoutRootPaths``
    Required?
        No
    Description
        Contains the list of paths handled by the layouts.

        .. note::

            At index ``10`` is the path to Formz layouts.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.view {
                layoutRootPaths {
                    20 = EXT:my_extension/Resources/Private/Layouts/Forms/
                }
            }

.. _viewPartialRootPaths:

Partial root paths
------------------

.. container:: table-row

    Property
        ``partialRootPaths``
    Required?
        No
    Description
        Contains the list of paths handled by the partials.

        .. note::

            At index ``10`` is the path to Formz partials.

        **Example:**

        .. code-block:: typoscript

            config.tx_formz.view {
                partialRootPaths {
                    20 = EXT:my_extension/Resources/Private/Partials/Forms/
                }
            }
