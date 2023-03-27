/*browser:true*/
/*global define*/
define(
	[
		'Magento_Checkout/js/view/payment/default',
		'Magento_Checkout/js/action/redirect-on-success',
		'mage/url'
	],
	function(
		Component,
		redirectOnSuccess,
		urlBuilder
	) {
		'use strict';

		return Component.extend({
			defaults: {
				template: 'Sparxpres_Websale/payment/xprespay-payment'
			},

			afterPlaceOrder: function () {
				redirectOnSuccess.redirectUrl = urlBuilder.build('/sparxpres/launchxprespay');
				this.redirectAfterPlaceOrder = true;
			}
		});
	}
);