# Page type used for Ajax calls.
FormzAjax = PAGE
FormzAjax {
    typeNum = 1473682545

    10 = USER_INT
    10 {
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        vendorName = Romm
        extensionName = Formz
        pluginName = AjaxValidation
        controller = AjaxValidation
        action = run
    }

    config {
        disableAllHeaderCode = 1
        debug = 0
        no_cache = 1
    }
}
