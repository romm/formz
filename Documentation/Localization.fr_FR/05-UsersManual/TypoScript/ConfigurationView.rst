.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _usersManual-typoScript-configurationView:

Vue
===

Pour être en mesure d'utiliser correctement toutes les fonctionnalités lors de l'intégration HTML des formulaires, il faudra configurer certains paramètres TypoScript.

Propriétés
----------

=============================================================== =====================================
Propriété                                                       Titre
=============================================================== =====================================
:ref:`classes <viewClasses>`                                    Liste des classes HTML dynamiques.

:ref:`layouts <viewLayouts>`                                    Liste des layouts utilisables.

:ref:`layoutRootPaths <viewLayoutRootPaths>`                    Chemins valables pour les layouts.

:ref:`partialRootPaths <viewPartialRootPaths>`                  Chemins valables pour les partials.
=============================================================== =====================================

-----

.. _viewClasses:

Classes dynamiques
------------------

.. container:: table-row

    Propriété
        ``classes``
    Requis ?
        Non
    Description
        Contient la liste des classes utilisables par le ViewHelper « :ref:`integratorManual-viewHelpers-class` ».

        Le premier niveau de cette configuration doit être une clé parmi les deux suivantes :

        * ``valid`` : contiendra toutes les classes qui seront activées lorsque le champ sera **valide**.

        * ``errors`` : contiendra toutes les classes qui seront activées lorsque le champ sera **invalide**.

        **Exemple :**

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

    Propriété
        ``layouts``
    Requis ?
        Non
    Description
        Contient la liste des layouts utilisables dans la propriété ``layout`` du ViewHelper « :ref:`integratorManual-viewHelpers-field` ».

        Les layouts sont divisés en groupes, puis en liste de layouts pour chacun des groupes.

        Dans chaque groupe, deux propriétés doivent être remplies :

        * ``templateFile`` : chemin vers le template qui sera utilisé par défaut par tous les layouts de ce groupe.

        * ``items`` : liste des layouts de ce groupe : la clé de chaque layout sera son identifiant.

          + ``templateFile`` : chemin vers le template qui sera utilisé par le layout (écrase la valeur de celui par défaut).

          + ``layout`` : chemin relatif vers le layout (doit être disponible dans les chemins définis par la propriété « :ref:`layoutRootPaths <viewLayoutRootPaths>` »).

        **Exemple :**

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

            Formz propose par défaut des layouts pour les frameworks CSS **Twitter Bootstrap** et **Foundation**. Lisez le chapitre « :ref:`@todo <>` » pour plus d'informations.

.. _viewLayoutRootPaths:

Chemins des layouts
-------------------

.. container:: table-row

    Propriété
        ``layoutRootPaths``
    Requis ?
        Non
    Description
        Contient la liste des chemins reconnus par les layouts.

        .. note::

            À l'index ``10`` se trouve le chemin vers les layouts de Formz.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.view {
                layoutRootPaths {
                    20 = EXT:my_extension/Resources/Private/Layouts/Forms/
                }
            }

.. _viewPartialRootPaths:

Chemins des partials
--------------------

.. container:: table-row

    Propriété
        ``partialRootPaths``
    Requis ?
        Non
    Description
        Contient la liste des chemins reconnus par les partials.

        .. note::

            À l'index ``10`` se trouve le chemin vers les partials de Formz.

        **Exemple :**

        .. code-block:: typoscript

            config.tx_formz.view {
                partialRootPaths {
                    20 = EXT:my_extension/Resources/Private/Partials/Forms/
                }
            }
