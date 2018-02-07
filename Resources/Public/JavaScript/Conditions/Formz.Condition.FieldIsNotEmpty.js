Fz.Condition.registerCondition(
	'Romm\\Formz\\Condition\\Items\\FieldIsNotEmptyCondition',
	/**
	 * @param {Formz.FormInstance} form
	 * @param {Object}             data
	 * @param {String}             data.fieldName
	 */
	function (form, data) {
        var flag = false;
		var field = form.getFieldByName(data['fieldName']);

		if (null !== field) {
		    var value = field.getValue();

            if (typeof value === 'object') {
                flag = (value.length !== 0);
            } else {
                flag = (value !== '');
            }
		} else {
		    flag = false;
        }

		return flag;
	}
);
