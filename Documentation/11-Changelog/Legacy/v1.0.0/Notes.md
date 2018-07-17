1.0.0 - 2017-03-23
------------------

First stable version! 🍻

After months of work on making the extension as reliable as possible (core refactoring, better architecture, hundreds of unit tests), the first stable version is finally out!

**Please note that major changes have been made since last beta version, you should check the breaking changes below (commits that begin with `[!!!]`).**

- **[[#48](https://github.com/romm/formz/pull/48)] [FEATURE] Handle `warning` and `notice` types in validation messages**

  This commit adds a more reliable handling of the warning and notice message types in validation rules.

  These messages won't block a validation (an error will), but can be used to deliver more information to the final user about actions done during the validation.

  Ajax requests are supported.

- **[[#49](https://github.com/romm/formz/pull/49)] [!!!][TASK] Change fields TypoScript configuration `activation` keys**

  For the fields activation configuration, the keys `items` and `condition` have been changed to `conditions` and `expression`.

  This makes more sense for what these configuration actually do.

  A depreciation message has been added to help converting old configuration to the new one.

- **[[#52](https://github.com/romm/formz/pull/52)] [!!!][TASK] Rename `section` to `slot`**

  The decision has been taken to rename the two view helpers `formz:section` and `formz:renderSection` to `formz:slot` and `formz:slot.render`.

  This helps to reduce the confusion with Extbase `f:section` and `f:render` view helpers.

  The argument `section` of the view helper `renderSlot` has also been renamed to `slot`.

  See examples below:

  Old:
  ```
  Template :
  ----------
  <formz:field name="myField" ...>
      <formz:section name="Field">
          <f:form.textField />
      </formz:section>
  </formz:field>

  Layout :
  --------
  <fieldset>
      <formz:renderSection section="Field" />
  </fieldset>
  ```

  New:
  ```
  Template :
  ----------
  <formz:field name="myField" ...>
      <formz:slot name="Field">
          <f:form.textField />
      </formz:slot>
  </formz:field>

  Layout :
  --------
  <fieldset>
      <formz:slot.render slot="Field" />
  </fieldset>
  ```

- **[[#53](https://github.com/romm/formz/pull/53)] [FEATURE] Introduce `slot.has` view helper**

  This is a conditional view helper, used to check if a slot has been defined in a field template. It allows changing the HTML rendering depending on the presence of the slot.

  **Example:**

  ```
  <div class="container">
      <formz:slot.has slot="Image">
          <div class="image">
              <formz:slot.render slot="Image" />
          </div>
      </formz:slot.has>
  </div>
  ```

  `<f:then>` and `<f:else>` work too!

  ```
  <div class="container">
      <formz:slot.has slot="Image">
          <f:then>
              <formz:slot.render slot="Image" />
          </f:then>
          <f:else>
              <img src="default-image.jpg" />
          </f:else>
      </formz:slot.has>
  </div>
  ```

- **[[#58](https://github.com/romm/formz/pull/58)] [!!!][TASK] Rename `formz` namespace to `fz`**

  The decision has been taken to rename the namespace `formz` to `fz` in the contexts below.

  The main reason is to improve readability: `fz` is far more ignorable by the eye than `formz` is. It also reduces the weight of the generated code.

  - **JavaScript**: the global namespace `Formz` is now accessible with `Fz`.

    Note that `Formz` is still accessible, but should be avoided.

    **Example:**

    ```javascript
    Fz.Form.get(...)
    ```

  - **CSS**: the entire list of data attributes that looked like `formz-*` are transformed to `fz-*`. This does affect generated CSS files.

    **Example:**

    ```css
    form[fz-value-gender="male"] {
        ...
    }
    ```

  - **Fluid**: the namespace `formz`, which was used in every template of the extension has been renamed to `fz`.

    **Example:**

    ```html
    {namespace fz=Romm\Formz\ViewHelpers}

    <fz:field name="Email">
        ...
    </fz:field>
    ```

- **[[58b0fe0](https://github.com/romm/formz/commit/58b0fe09793a84eb7784fc2f401ccb11b281e45e)] [!!!][TASK] Remove method `onRequiredArgumentIsMissing`**

    This method has nothing to do with the form API, and is entirely and only bound to controller behaviours.

    If you need it, please paste the code in your own controller.

- **[[0f73599](https://github.com/romm/formz/commit/0f73599dad09d536568d767b3626ef47e660e048)] [!!!][TASK] Change form TypoScript configuration `activationCondition`**

  The TypoScript configuration `activationCondition` has been renamed to `conditionList`.

  This makes more sense for what this configuration actually do.

  A depreciation message has been added to help converting old configuration to the new one.

- **[[e32f00c](https://github.com/romm/formz/commit/e32f00c5a0768070a084be17156f814d51ad342c)] [!!!][TASK] Rename `feedback` to `message`**

  The old decision to use the word "feedback" for actual validation messages was a mistake that could lead to misunderstanding. Indeed, TYPO3 core uses the word "messages" everywhere for errors, warnings and notices, and never "feedback".

  It has been decided to rename it before the stable version is out.
