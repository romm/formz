1.1.0 - 2017-04-08
------------------

TYPO3 8.7 LTS is now officially supported! 🎉

Some bug fixes have been committed as well:
 
- **[[915ec9a](https://github.com/romm/formz/commit/915ec9aa8aa5b14c53819b80f1cf0c9e270a53fa)] [BUGFIX] Use slot rendering context instance to inject variables**

  In some cases, injecting variables in the template variable container that was fetched from the controller context would not work.
  
  This commit changes the way a slot controller context is stored, to solve this issue.

- **[[30b987f](https://github.com/romm/formz/commit/30b987faeeb7e3d2ec20d843a48a3a8afb1a3b27)] [BUGFIX] Use one view instance per layout**

  When using fields with different templates, the source of the template file was not reloaded, causing issues.
  
  Instead of using the same view instance everytime, they are stored in local cache based on their template file.

- **[[2141fc8](https://github.com/romm/formz/commit/2141fc87763745764d296976cffcd61797c32289)] [BUGFIX] Fix cHash issue in Ajax request URI**

  Adding an argument forces the URI to calculate a cHash (it was not the case previously). Unfortunately, in TYPO3 8 the cHash is required in the request, and would cause a fatal error when it's missing.
  
  This may be a temporary fix, as this is probably not the best way to do it.
