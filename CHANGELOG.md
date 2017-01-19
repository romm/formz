# ![Formz](Documentation/Images/formz-icon@medium.png) Formz - Changelog

0.3.1 - 2016-01-05
------------------

This release fixes two issues, updating is recommended.

----

- **[[a71a039](https://github.com/romm/formz/commit/a71a039ae4b56cf4f86cc428fc66e6312b1ddd52)] [BUGFIX] Delete required property for activation configuration**

  The property `items` for activations (fields and conditions) was flagged as required, that is not true and has been removed.

- **[[55769f9](https://github.com/romm/formz/commit/55769f93eb6ce16d8a7544250181eea51b02874d)] [BUGFIX] Fix page renderer singleton issue**

  The page renderer must be instantiated with `GeneralUtility::makeInstance()` instead of Extbase object manager (or with an inject function).

  This is due to some TYPO3 low level behaviour which overrides the page renderer singleton instance, whenever a new request is used. The problem is that the instance is not updated on Extbase side.

  Using Extbase injection can lead to old page renderer instance being used, resulting in a leak of assets inclusion, and maybe more issues.

0.3.0 - 2016-12-10
------------------

This release fixes two minor issues, and one major issue. Please update to this new version!

The road to full unit tests coverage continues.

----

- **[[45020d6](https://github.com/romm/formz/commit/45020d6e62ae2e30024188020b57d6dea8206d11)] [TASK] Introduce asset handler connectors**

  Big refactoring of the asset handlers usage: connectors have been introduced, in order to split the several asset handlers usage. A whole tests suite has been written for these connectors.

  A refactoring of the condition API has also been made, to help testing out some functionnality of the asset handler connectors, but also to resolve an issue that has been discovered during development (#28).

- **[[35528cf](https://github.com/romm/formz/commit/35528cf7a64501fccf091a86ffd3394e8d2a32dc)] [BUGFIX] Fix the js email validator regex (@Mopolo)**

- **[[e8c9a4b](https://github.com/romm/formz/commit/e8c9a4bd91261777587f6c16358a381e76b6daf4)] [BUGFIX] Fix double event issue for text inputs**

0.2.0 - 2016-11-10
------------------

This release fixes two severe issues (see below), it is **strongly recommended** to upgrade to this version.

A new condition type has also been introduced: `fieldIsEmpty`, which can be used to check whether a field has no value set.

A bunch of unit tests are also embedded in this version, making a step forward the first stable version, which will be released when the tests coverage reaches a correct rate.

----

**Major commits list:**

- **[[5849c42](https://github.com/romm/formz/commit/5849c4241946e673cf0895a1d2c2440eb697a0a3)] [FEATURE] Add new condition `fieldIsEmpty`**

  Adding a new condition, with its own JavaScript connector. It is verified when a field has no value, but also when a multiple checkbox has no value checked.

- **[[3e70d83](https://github.com/romm/formz/commit/3e70d8364320a050c59699f66be6c0e8b2f9ce6f)] [BUGFIX] Fix wrong JavaScript call**

  A JavaScript debug statement was using the `Debug` library directly, instead of the low-level `debug` function.

  It could cause an error when the `Debug` library was not loaded (when the debug mode is not enabled in Formz configuration).

- **[[#26](https://github.com/romm/configuration_object/pull/26)] [BUGFIX] Fix persistent option in field ViewHelper**

  When using the ViewHelper `<formz:option>` inside `<formz:field>`, the option would not be deleted after the whole field is processed, resulting in unwanted options in later fields, which could cause pretty ugly behaviours.

  For instance, a field could be flagged as required even if it is not.

  In the following example, the option `required` would have been present for the field `bar` (it is now fixed by this commit).

  ```
  <formz:field name="foo">
      <formz:option name="required" value="1" />
  
      [...]
  </formz:field>
  
  <formz:field name="bar">
      [...]
  </formz:field>
  ```

0.1.1 - 2016-10-10
------------------

- **[[#1](https://github.com/romm/configuration_object/pull/1)] [TASK] Implement Travis & Coveralls integration**

  A continuous integration is now up and running on the Git repository.

  Unit tests are being written.

-----

- **[[#5](https://github.com/romm/configuration_object/pull/5)] [DOC] Fix typos in the chapter 5**
- **[[#7](https://github.com/romm/configuration_object/pull/7)] [DOC] Fix typos in the chapter 6**
- **[[#10](https://github.com/romm/configuration_object/pull/10)] [DOC] Fix typos in the chapter 7**
- **[[#11](https://github.com/romm/configuration_object/pull/11)] [DOC] Fix typos in the chapter 8**
- **[[#12](https://github.com/romm/configuration_object/pull/12)] [DOC] fix typos in the readme file**

  Many english mistakes were corrected in the documentation. Thanks to [@Mopolo](https://github.com/Mopolo) for his help!
