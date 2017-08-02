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

  It could cause an error when the `Debug` library was not loaded (when the debug mode is not enabled in FormZ configuration).

- **[[#26](https://github.com/romm/formz/pull/26)] [BUGFIX] Fix persistent option in field ViewHelper**

  When using the ViewHelper `<fz:option>` inside `<fz:field>`, the option would not be deleted after the whole field is processed, resulting in unwanted options in later fields, which could cause pretty ugly behaviours.

  For instance, a field could be flagged as required even if it is not.

  In the following example, the option `required` would have been present for the field `bar` (it is now fixed by this commit).

  ```
  <fz:field name="foo">
      <fz:option name="required" value="1" />

      [...]
  </fz:field>

  <fz:field name="bar">
      [...]
  </fz:field>
  ```
