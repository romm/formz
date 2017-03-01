Formz.Condition.registerCondition(
	'Romm\\Formz\\Condition\\Items\\FieldHasValueCondition',
	/**
	 * @param {Formz.FormInstance} form
	 * @param {Object}             data
	 * @param {String}             data.fieldName
	 * @param {String}             data.fieldValue
	 */
	function (form, data) {
		var flag = false;
		var field = form.getFieldByName(data['fieldName']);
		var value = data['fieldValue'];

		if (null !== field) {
			if (value !== '') {
			    var fieldValue = field.getValue();

                flag = (typeof fieldValue === 'string')
                    ? fieldValue === value
                    : (Formz.commaSeparatedValues(fieldValue).search(value) > -1);
            } else {
				flag = (Formz.commaSeparatedValues(field.getValue()) == '')
            }
		}

		return flag;
	}
);
