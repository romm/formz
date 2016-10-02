Formz.Condition.registerCondition(
	'Romm\\Formz\\Condition\\Items\\FieldHasErrorCondition',
	/**
	 * @param {Formz.FormInstance} form
	 * @param {Object}             data
	 * @param {String}             data.fieldName
	 */
	function (form, data) {
		var flag = false;
		var field = form.getFieldByName(data['fieldName']);
		var validationName = form.getFieldByName(data['validationName']);
		var errorName = form.getFieldByName(data['errorName']);

		if (null !== field) {
			flag = field.hasError(validationName, errorName)
		}

		return flag;
	}
);
