.. include:: ../../Includes.txt

.. _cheatSheets-css:

Anti-sèche HTML/CSS
===================

Auto-complétion PhpStorm
------------------------

FormZ propose un schéma permettant aux IDE comme PhpStorm de fournir une auto-complétion des ViewHelper dans les templates Fluid.

Vous pouvez retrouver un guide sur l'implémentation des XSD dans les IDE ici : https://github.com/FluidTYPO3/schemaker/blob/development/README.md#how-to-use-xsd-in-ide

**Exemple :**

.. code-block:: html

    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns:fz="http://typo3.org/ns/Romm/Formz/ViewHelpers">

        <f:layout name="MyLayout" />

        <f:section name="MySection">
            <fz:form name="myForm">
                ...
            </fz:form>
        </f:section>

    </html>


Attributs « data »
------------------

Liste des attributs utilisés sur la balise ``<form>`` et pouvant être utilisés en CSS :

* ``fz-valid`` : lorsque tous les champs sont valides ;

* ``fz-value-{field-name}="value"`` : valeur actuelle du champ ;

* ``fz-valid-{field-name}="1"`` : le champ est valide (aucune erreur de validation) ;

* ``fz-error-{field-name}="1"`` : le champ contient au moins une erreur ;

* ``fz-error-{field-name}-{rule-name}-{message-key}`` : le champ contient l'erreur ``{rule-name}`` avec le message ``{message-key}`` ;

* ``fz-submission-done`` : le formulaire a été soumis ;

* ``fz-submitted`` : le formulaire est en train d'être soumis.

**Exemple de code CSS correspondant :**

Dans l'exemple ci-dessous, le bloc ``info-customer`` sera masqué tant que le bouton « No » n'est pas sélectionné. Cela revient à dire qu'il ne sera affiché que si le bouton « No » est sélectionné.

.. code-block:: css

    form[name="myForm"]:not([fz-value-is-customer="0"]) .info-customer {
        display: none;
    }

.. code-block:: html

    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:form name="myForm" action="submitForm">
        <fz:field name="isCustomer" layout="default">
            <fz:slot name="Field">
                Are you a customer?
                <br />
                <f:form.radio property="isCustomer" value="1" />&nbsp;Yes
                <br />
                <f:form.radio property="isCustomer" value="0" />&nbsp;No
            </fz:slot>
        </fz:field>

        <div class="info-customer">
            You can subscribe easily to our service <a href="...">by clicking here</a>.
        </div>
    </fz:form>

Comportement de chargement
--------------------------

* L'attribut ``fz-loading`` est rajouté :

  1. À la balise ``<form>`` lorsque le formulaire est en train d'être **vérifié**. Cela peut être utilisé par exemple pour désactiver le bouton de soumission, le temps que les champs soient validés.

     .. attention::

         Un autre attribut est utilisé à la **soumission** du formulaire (lorsque tous les champs sont valides), voyez plus bas.

     **Exemple :**

     .. code-block:: css

         .formz[fz-loading] input[type="submit"] {
             display: none;
         }

  2. Au conteneur d'un champ lorsqu'il est en train d'être validé. Cela peut être utilisé par exemple pour afficher un cercle de chargement lorsqu'une requête Ajax est en cours.

* L'attribut ``fz-submitted`` est rajouté à la balise ``<form>`` lorsque le formulaire est en train d'être soumis (lorsque tous les champs ont été validés).
