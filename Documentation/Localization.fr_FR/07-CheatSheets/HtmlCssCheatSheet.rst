.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _cheatSheets-css:

Anti-sèche HTML/CSS
===================

Auto-complétion PhpStorm
------------------------

Formz propose un schéma permettant aux IDE comme PhpStorm de fournir une auto-complétion des ViewHelper dans les templates Fluid.

Vous pouvez retrouver un guide sur l'implémentation des XSD dans les IDE ici : https://github.com/FluidTYPO3/schemaker/blob/development/README.md#how-to-use-xsd-in-ide

**Exemple :**

.. code-block:: html

    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns:formz="http://typo3.org/ns/Romm/Formz/ViewHelpers">

        <f:layout name="MyLayout" />

        <f:section name="MySection">
            <formz:form name="myForm">
                ...
            </formz:form>
        </f:section>

    </html>


Attributs « data »
------------------

Liste des attributs utilisés sur la balise ``<form>`` et pouvant être utilisés en CSS :

* ``formz-valid`` : lorsque tous les champs sont valides ;

* ``formz-value-{field-name}="value"`` : valeur actuelle du champ ;

* ``formz-valid-{field-name}="1"`` : le champ est valide (aucune erreur de validation) ;

* ``formz-error-{field-name}="1"`` : le champ contient au moins une erreur ;

* ``formz-error-{field-name}-{rule-name}-{message-key}`` : le champ contient l'erreur ``{rule-name}`` avec le message ``{message-key}`` ;

* ``formz-submission-done`` : le formulaire a été soumis ;

* ``formz-submitted`` : le formulaire est en train d'être soumis.

**Exemple de code CSS correspondant :**

Dans l'exemple ci-dessous, le bloc ``info-customer`` sera masqué tant que le bouton « No » n'est pas sélectionné. Cela revient à dire qu'il ne sera affiché que si le bouton « No » est sélectionné.

.. code-block:: css

    form[name="myForm"]:not([formz-value-is-customer="0"]) .info-customer {
        display: none;
    }

.. code-block:: html

    {namespace formz=Romm\Formz\ViewHelpers}

    <formz:form name="myForm" action="submitForm">
        <formz:field name="isCustomer" layout="default">
            <formz:slot name="Field">
                Are you a customer?
                <br />
                <f:form.radio property="isCustomer" value="1" />&nbsp;Yes
                <br />
                <f:form.radio property="isCustomer" value="0" />&nbsp;No
            </formz:slot>
        </formz:field>

        <div class="info-customer">
            You can subscribe easily to our service <a href="...">by clicking here</a>.
        </div>
    </formz:form>

Comportement de chargement
--------------------------

* L'attribut ``formz-loading`` est rajouté :

  1. À la balise ``<form>`` lorsque le formulaire est en train d'être **vérifié**. Cela peut être utilisé par exemple pour désactiver le bouton de soumission, le temps que les champs soient validés.

     .. attention::

         Un autre attribut est utilisé à la **soumission** du formulaire (lorsque tous les champs sont valides), voyez plus bas.

     **Exemple :**

     .. code-block:: css

         .formz[formz-loading] input[type="submit"] {
             display: none;
         }

  2. Au conteneur d'un champ lorsqu'il est en train d'être validé. Cela peut être utilisé par exemple pour afficher un cercle de chargement lorsqu'une requête Ajax est en cours.

* L'attribut ``formz-submitted`` est rajouté à la balise ``<form>`` lorsque le formulaire est en train d'être soumis (lorsque tous les champs ont été validés).
