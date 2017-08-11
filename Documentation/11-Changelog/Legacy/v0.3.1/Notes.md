0.3.1 - 2017-01-05
------------------

This release fixes two issues, updating is recommended.

----

- **[[a71a039](https://github.com/romm/formz/commit/a71a039ae4b56cf4f86cc428fc66e6312b1ddd52)] [BUGFIX] Delete required property for activation configuration**

  The property `items` for activations (fields and conditions) was flagged as required, that is not true and has been removed.

- **[[55769f9](https://github.com/romm/formz/commit/55769f93eb6ce16d8a7544250181eea51b02874d)] [BUGFIX] Fix page renderer singleton issue**

  The page renderer must be instantiated with `GeneralUtility::makeInstance()` instead of Extbase object manager (or with an inject function).

  This is due to some TYPO3 low level behaviour which overrides the page renderer singleton instance, whenever a new request is used. The problem is that the instance is not updated on Extbase side.

  Using Extbase injection can lead to old page renderer instance being used, resulting in a leak of assets inclusion, and maybe more issues.
