config.tx_formz {
    # Below is a list of very common fields types which can be used almost anywhere with the same rule.
    # Feel free to "import" these settings directly in your forms configuration.
    # Example :
    # config.tx_formz.My\Custom\Form.fields.email < config.tx_formz.fields.email
    fields {
        ########################
        # Email
        #  > Valid email address
        ###
        email {
            validation {
                email < config.tx_formz.validators.email
            }
        }
    }
}