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
