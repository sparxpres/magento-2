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
				type: 'sparxpres_payment',
				component: 'Sparxpres_Websale/js/view/payment/method-renderer/sparxpres-payment-method'
			},
			{
				type: 'xprespay_payment',
				component: 'Sparxpres_Websale/js/view/payment/method-renderer/xprespay-payment-method'
			}
		);
		/** Add view logic here if needed */
		return Component.extend({});
	}
);
