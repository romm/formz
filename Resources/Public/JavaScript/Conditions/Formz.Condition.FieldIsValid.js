Formz.Condition.registerCondition(
	'Romm\\Formz\\Condition\\Items\\FieldIsValidCondition',
	/**
	 * @param {Formz.FormInstance} form
	 * @param {Object}             data
	 * @param {String}             data.fieldName
	 */
	function (form, data) {
		var flag = false;
		var field = form.getFieldByName(data['fieldName']);

		if (null !== field) {
			flag = field.isValid()
		}

		return flag;
	}
);
