Fz.Condition.registerCondition(
    'Romm\\Formz\\Condition\\Items\\FieldCountValuesCondition',
    /**
     * @param {Formz.FormInstance} form
     * @param {Object}             data
     * @param {String}             data.fieldName
     * @param {String}             data.minimum
     * @param {String}             data.maximum
     */
    function (form, data) {
        var field = form.getFieldByName(data['fieldName']);
        var minimum = data['minimum'];
        var maximum = data['maximum'];

        if (null === field) {
            return false;
        }

        var fieldValue = field.getValue();
        var values = [];

        if (fieldValue !== ''
            && fieldValue.length !== 0
        ) {
            values = Fz.commaSeparatedValues(fieldValue).split(',');
        }

        return !(maximum && values.length > maximum)
            && !(minimum && values.length < minimum);
    }
);
