define(['jquery'], function ($) {
	'use strict';
	return function (target) {
		$.validator.addMethod(
			'validate-hex-color-code',
			function (value) {
				if (value && value.length > 0) {
					const hexColorRegExp = /^#([A-Fa-f0-9]{3}){1,2}$/;
					return hexColorRegExp.test(value);
				}
				return true;
			},
			$.mage.__('Please enter a valid hex color code (# followed by 3 or 6 hexadecimal digits)')
		);
		return target;
	};
});