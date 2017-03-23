Fz.Validation.registerValidator(
    'Romm\\Formz\\Validation\\Validator\\RegexValidator',
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
            var pattern = states['configuration']['options']['pattern'];
            var options = states['configuration']['options']['options'];
            var regex = new RegExp(pattern, options);
            if (false === regex.test(value)) {
                states['result'].addError({
                    name: 'default',
                    message: states['configuration']['messages']['default']
                });
            }
        }

        callback();
    }
);
