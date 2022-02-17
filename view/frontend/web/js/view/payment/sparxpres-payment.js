/*browser:true*/
/*global define*/
define(
	[
		'uiComponent',
		'Magento_Checkout/js/model/payment/renderer-list'
	],
	function (
		Component,
		rendererList
	) {
		'use strict';

		rendererList.push(
			{
				type: 'sparxpres-payment',
				component: 'Sparxpres_Websale/js/view/payment/method-renderer/sparxpres-payment-renderer'
			}
		);

		/** Add view logic here if needed */
		return Component.extend({});
	}
);