Fz.Validation.registerValidator(
    'Romm\\Formz\\Validation\\Validator\\EmailValidator',
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
            // @see http://emailregex.com/
            var pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;
            if (false === pattern.test(value)) {
                states['result'].addError({
                    name: 'default',
                    message: states['configuration']['messages']['default']
                });
            }
        }

        callback();
    }
);
