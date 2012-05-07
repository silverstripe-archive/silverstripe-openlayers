<?php

class OpacityNumericField extends NumericField {
	
	
	function jsValidation() {
		$formID = $this->form->FormName();
		$error = 'is not a number greater that 0 and less-equals than 1, only numbers between 0 and 1 can be accepted for this field.';
		$jsFunc =<<<JS
Behaviour.register({
	"#$formID": {
		validateNumericField: function(fieldName) {
				el = _CURRENT_FORM.elements[fieldName];
				if(!el || !el.value) return true;
				
			 	if(!isNaN(el.value)) {
			 		return true;
				}
				
			 	if(el.value > 0 || el.value <= 1) {
			 		return true;
			 	} else {
					validationError(el, "'" + el.value + "' $error","validation");
			 		return false;
			 	}
			}
	}
});
JS;

		Requirements::customScript($jsFunc, 'func_validateNumericField');

		//return "\$('$formID').validateNumericField('$this->name');";
		return <<<JS
if(typeof fromAnOnBlur != 'undefined'){
	if(fromAnOnBlur.name == '$this->name')
		$('$formID').validateNumericField('$this->name');
}else{
	$('$formID').validateNumericField('$this->name');
}
JS;
	}
	
	/** PHP Validation **/
	function validate($validator){
		if($this->value && !is_numeric(trim($this->value))){
 			$validator->validationError(
 				$this->name,
				sprintf(
					"'%s' is not a number greater that 0 and less-equals than 1, only numbers can be accepted for this field.",
					$this->value
				),
				"validation"
			);
			return false;
		} 
		if ($this->value >= 0 && $this->value <= 1) {
			return true;
		}else {
 			$validator->validationError(
 				$this->name,
				sprintf(
					"'%s' is not a number greater that 0 and less-equals than 1, only numbers can be accepted for this field.",
					$this->value
				),
				"validation"
			);
		return false;
		}
	}
	
	function dataValue() {
		return (is_numeric($this->value) && $this->value <= 1 && $this->value >= 0) ? $this->value : 0;
	}
}