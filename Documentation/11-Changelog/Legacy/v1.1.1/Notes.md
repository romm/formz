1.1.1 - 2017-06-12
------------------

Contains two bug-fixes:

- **[[e225096](https://github.com/romm/formz/commit/e2250962f9ae92b5fa00876217e59d7f6a649f7a)] [BUGFIX] Fix nested field layouts not properly rendered**

  This commit allows several field layouts level: a field rendered with the field view helper can now render another field within its slots.
  
  Example:
  
  ```
  <fz:field name="foo">
      <fz:slot name="Field">
          Bacon ipsum dolor...
  
          <fz:field name="bar">
              ...
          </fz:field>
      </fz:slot>
  </fz:field>
  ```

- **[[7da753a](https://github.com/romm/formz/commit/7da753ac0e7115dc84a5a964884051ca5c91ca84)] [BUGFIX] Fix layout/partial root paths not merged in field layouts**

  This commit fixes the situation where a slot in a field layout tries to use a partial from the actual rendering context: until now only the partials configured in the FormZ view TypoScript configuration were supported, resulting in a fatal error.
  
  The paths are now merged together, giving access to both of them.
  
  The same behaviour is done for layouts.
  
- **[[9be619c](https://github.com/romm/formz/commit/9be619cf5ebdf567404203aaa748a066d413cc05)] [TASK] Mark form validation data functions as deprecated**
