Fz.Validation.registerValidator(
    'Romm\\Formz\\Validation\\Validator\\RequiredValidator',
    /**
     * @param {string}                   value
     * @param {Function}                 callback
     * @param {Object}                   states
     * @param {Formz.ResultInstance}     states.result
     * @param {Object}                   states.data
     * @param {string}                   states.validatorName
     * @param {object}                   states.configuration
     */
    function (value, callback, states) {
        if (value === null || value === '') {
            states['result'].addError({
                name: 'default',
                message: states['configuration']['messages']['default']
            });
        }

        callback();
    }
);
