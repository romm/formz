The schema `Formz.xsd` was generated using TYPO3 extension `fluidtypo3/schemaker`:

- On TER: https://typo3.org/extensions/repository/view/schemaker
- On Packagist: https://packagist.org/packages/fluidtypo3/schemaker
- Documentation: https://github.com/FluidTYPO3/schemaker/blob/development/README.md

Run the following command to update the schema whenever a ViewHelper from FormZ is added/modified:

`./typo3/cli_dispatch.phpsh extbase schema:generate "Romm.Formz" > typo3conf/ext/formz/Resources/Private/Schemas/Formz.xsd`
