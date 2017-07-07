.. include:: ../../../Includes.txt

.. _developerManual-php-validator:

Validateur
==========

Les validateurs sont utilisés pour vérifier la valeur des champs envoyés à la soumission d'un formulaire. Leur comportement est quasiment identique aux validateurs classiques de TYPO3, mais ils disposent de **quelques fonctionnalités supplémentaires**.

Pour configurer les validateurs utilisables dans la configuration des formulaires, consultez le chapitre « :ref:`usersManual-typoScript-configurationValidators` ».

Vous avez la possibilité de créer vos propres validateurs selon vos besoins ; veillez à ce qu'ils aient comme parent ``Romm\Formz\Validation\Validator\AbstractValidator``, et à utiliser correctement les fonctions de l'API.

API
^^^

Les validateurs de FormZ vous donnent accès aux variables/fonctions suivantes :

- :ref:`$form <validator-form>`
- :ref:`$fieldName <validator-fieldName>`
- :ref:`$supportedMessages <validator-supportedMessages>`
- :ref:`$supportsAllMessages <validator-supportsAllMessages>`
- :ref:`$javaScriptValidationFiles <validator-javaScriptValidationFiles>`
- :ref:`addError($key, $code, array $arguments) <validator-addError>`

-----

.. _validator-form:

Instance du formulaire
----------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $form;
    Type
        :php:`Romm\Formz\Form\FormInterface`
    Description
        Vous avez accès via ``$this->form`` à **l'instance du formulaire soumis**. Cela vous permet par exemple d'appliquer certaines règles en fonction des valeurs d'autres champs du formulaire.

        .. warning::

            ``$this->form`` a un accès en **lecture seule**, vous ne pouvez pas modifier les valeurs des champs.

.. _validator-fieldName:

Nom du champ
------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $fieldName;
    Type
        :php:`string`
    Description
        Contient le nom du champ qui est actuellement validé par ce validateur.

.. _validator-supportedMessages:

Liste des messages supportés
----------------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $supportedMessages = [];
    Type
        :php:`array`
    Description
        Dans FormZ, les validateur fonctionnent avec des **messages pré-configurés**. En effet, un validateur peut renvoyer différents messages d'erreurs ; il devra définir à l'avance quels messages sont utilisables : une clé de message, et sa configuration.

        Utilisez la variable de classe ``$supportedMessages`` pour définir la liste de messages d'erreurs utilisés par le validateur. Inspirez-vous de l'exemple suivant pour respecter la structure :

        Les valeurs de ces messages pourront être surchargés par la configuration TypoScript des champs de formulaires.

        .. code-block:: php

            protected $supportedMessages = [
               // "default" est l'index du message.
               'default'    => [
                  // "key" représente la clé LLL du message.
                  'key'        => 'validator.form.contains_values.error',

                  // "extension" contient le nom de l'extension utilisée pour
                  // retrouver la clé LLL du message.
                  // Si vide, l'extension "FormZ" est utilisée.
                  'extension'    => null
               ],
               'test'    => [
                  // Si vous renseignez "value", la valeur sera directement
                  // utilisée et le système ne cherchera pas de traduction.
                  'value'        => 'Test de message !'
               ]
            ];

.. _validator-supportsAllMessages:

Supporter tous les messages
---------------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected $supportsAllMessages = false;
    Type
        :php:`bool`
    Description
        Si jamais votre validateur doit ajouter dynamiquement des messages d'erreurs (par exemple lors de l'utilisation d'un web service), vous pouvez passer cette valeur à ``true``. Préférez la laisser à ``false`` par défaut, si vous n'êtes pas certain d'en avoir besoin.

.. _validator-addError:

Ajouter un erreur
-----------------

.. container:: table-row

    Fonction
        .. code-block:: php

            $this->addError($key, $code, array $arguments);
    Retour
        /
    Paramètres
        - ``$key`` : la clé du message, doit être une clé du tableau ``$supportedMessages``.
        - ``$code`` : le code de l'erreur, par convention il s'agira du timestamp actuel au moment où le développeur rajoute l'erreur.
        - ``$arguments`` : les éventuels arguments qui seront remplacés dans le texte du message.
    Description
        Vous devrez utiliser cette fonction pour rajouter une erreur si la valeur ne passe pas la validation.

.. _validator-javaScriptValidationFiles:

Lier un fichier JavaScript
--------------------------

.. container:: table-row

    Propriété
        .. code-block:: php

            protected static $javaScriptValidationFiles = [];
    Type
        :php:`array`
    Description
        Contient la liste des fichiers JavaScript qui émuleront ce validateur dans le navigateur du client. Remplissez juste ce tableau, FormZ s'occupera de les importer automatiquement.

        Ces fichiers devront contenir la déclaration de la version JavaScript du validateur en question, en utilisant la fonction :ref:`Fz.Validation.registerValidator() <developerManual-javaScript-validation-registerValidator>`.

        **Exemple :**

        .. code-block:: php

            protected static $javaScriptValidationFiles = [
                'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.Required.js'
            ];

-----

Exemple de validateur
^^^^^^^^^^^^^^^^^^^^^

Vous retrouverez ci-dessous un exemple de validateur.

.. code-block:: php

    <?php
    namespace Romm\Formz\Validation\Validator;

    use Romm\Formz\Validation\Validator\AbstractValidator;

    class ContainsValuesValidator extends AbstractValidator {
        /**
         * @inheritdoc
         */
        protected $supportedOptions = [
           'values' => [
              [],
              'The values that are accepted',
              'array',
              true
           ]
        ];

        /**
         * @inheritdoc
         */
        protected $supportedMessages = [
           'default'    => [
              'key'        => 'validator.form.contains_values.error',
              'extension'    => null
           ]
        ];

        /**
         * @inheritdoc
         */
        public function isValid($valuesArray)
        {
           $flag = false;

           if (is_array($valuesArray)) {
              foreach ($valuesArray as $value) {
                 if (in_array($value, $this->options['values'])) {
                    $flag = true;
                    break;
                 }
              }
           }

           if (false === $flag) {
              $this->addError(
                 'default'
                 1445952458,
                 [implode(
                   ', ',
                   $this->options['values']
                )]
              );
           }
        }
    }
