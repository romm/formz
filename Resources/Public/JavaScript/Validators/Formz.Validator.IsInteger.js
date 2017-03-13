Fz.Validation.registerValidator(
    'Romm\\Formz\\Validation\\Validator\\IsIntegerValidator',
    /**
     * @param {string}                   value
     * @param {Function}                 callback
     * @param {Object}                   states
     * @param {Formz.ResultInstance}     states.result
     * @param {Object}                   states.data
     * @param {string}                   states.validatorName
     * @param {object}                   states.configuration
     */
    function(value, callback, states) {
        if (value !== '') {
            if (isNaN(parseInt(value))
                || !(value == parseInt(value))
            ) {
                states['result'].addError({
                    name: 'default',
                    message: states['configuration']['messages']['default']
                });
            }
        }

        callback();
    }
);
