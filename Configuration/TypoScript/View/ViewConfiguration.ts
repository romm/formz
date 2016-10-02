config.tx_formz {
    view {
        layoutRootPaths {
            10 = EXT:formz/Resources/Private/Layouts/
        }

        partialRootPaths {
            10 = EXT:formz/Resources/Private/Partials/
        }

        layouts {
            default {
                templateFile = EXT:formz/Resources/Private/Templates/Default/Default.html

                items {
                    default {
                        layout = Default/Default
                    }
                }
            }
        }
    }
}