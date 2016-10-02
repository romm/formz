# Page type used for Ajax calls.
FormzAjax = PAGE
FormzAjax {
    typeNum = 1473682545

    10 = USER_INT
    10.userFunc = Romm\Formz\Validation\AjaxFieldValidation->run

    config {
        disableAllHeaderCode = 1
        additionalHeaders = Content-type:application/json
        xhtml_cleaning = 0
        admPanel = 0
        no_cache = 1
        debug = 0
    }
}
