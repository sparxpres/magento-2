define(['jquery'], function($){
	"use strict";

	$(document).ready(function() {
		let spxLoanCalcEngine = {
			debug: false,
			prevCalculationPeriod: 0,
			prevCalculationPrice: 0,
			loanInfoCacheMap: new Map(),
			$sparxpresWebSale: null,
			$sliderPeriodElem: 0,
			$dropdownPeriodElem: 0,

			init: function () {
				/**
				 * Add event listener for select period change
				 */
				window.addEventListener("sparxpresSelectRuntimeChange", function (event) {
					if (spxLoanCalcEngine.debug) console.log("sparxpresSelectRuntimeChange event caught");
					spxLoanCalcEngine.loanCalculation(event.detail && event.detail.period ? parseInt(event.detail.period) : null);
				});

				/**
				 * Add event listener for slider period change
				 */
				window.addEventListener("sparxpresSliderRuntimeChange", function (event) {
					if (spxLoanCalcEngine.debug) console.log("sparxpresSliderRuntimeChange event caught");
					spxLoanCalcEngine.loanCalculation(event.detail && event.detail.period ? parseInt(event.detail.period) : null);
				});

				/**
				 * Add event listener for variation products
				 */
				window.addEventListener("sparxpresRuntimeRecalculate", function (event) {
					if (spxLoanCalcEngine.debug) console.log("sparxpresRuntimeRecalculate event caught");
					let _price = event.detail && event.detail.price ? parseInt(event.detail.price) : null;
					let _period = event.detail && event.detail.period ? parseInt(event.detail.period) : null;
					spxLoanCalcEngine.loanCalculation(_period, _price);
				});

				/**
				 * Add event listener for modal information page
				 */
				window.addEventListener("sparxpresInformationPageOpen", function (event) {
					if (spxLoanCalcEngine.debug) console.log("sparxpresInformationPageOpen event caught..");
					let infoModal = $("#sparxpresInformationPageModal");
					if (!infoModal.length) {
						infoModal = $("<div></div>")
							.attr("id", "sparxpresInformationPageModal")
							.attr("role", "dialog")
							.attr("tabindex", "-1")
							.attr("aria-modal", "true");
						$("body").append(infoModal);
					}

					if (infoModal.length) {
						if (infoModal.is(":empty")) {
							if (spxLoanCalcEngine.debug) console.log("sparxpresInformationPage not loaded, load and show it..");
							infoModal.html("<div class=\"Sparxpres__modal-content\">" +
								"<span class=\"Sparxpres__modal-close\" onclick=\"document.getElementById('sparxpresInformationPageModal').style.display='none';\">&times;</span>" +
								"<div class=\"Sparxpres__dynamic-content\"></div>" +
								"</div>");

							infoModal.show();
							spxLoanCalcEngine.loadPageInformation($("#sparxpres_web_sale").data("linkId"));
						} else {
							if (spxLoanCalcEngine.debug) console.log("sparxpresInformationPage already loaded, show it..");
							infoModal.show();
						}
					}
				});
			},

			loanCalculation: function (period = null, price = null) {
				let webSaleElem = spxLoanCalcEngine._getSparxpresWebSaleElement();
				if (!webSaleElem || !webSaleElem.data()) {
					if (spxLoanCalcEngine.debug) console.log("loanCalculation returning because element was not found or has not data attributes.");
					return;
				}

				if (spxLoanCalcEngine.prevCalculationPeriod === 0) spxLoanCalcEngine.prevCalculationPeriod = parseInt(webSaleElem.data("defaultPeriod")) || 0;
				if (spxLoanCalcEngine.prevCalculationPrice === 0) spxLoanCalcEngine.prevCalculationPrice = parseInt(webSaleElem.data("price")) || 0;

				let linkId = webSaleElem.data("linkId");
				let dynamicPeriod = parseInt(webSaleElem.data("dynamicPeriod")) || 0;

				period = period || spxLoanCalcEngine.prevCalculationPeriod;
				price = price || spxLoanCalcEngine.prevCalculationPrice;
				if (spxLoanCalcEngine.debug) console.log("loanCalculation period is %d price is %d", period, price);

				if (!dynamicPeriod || spxLoanCalcEngine.prevCalculationPrice === 0 || spxLoanCalcEngine.prevCalculationPrice === price) {
					spxLoanCalcEngine._callRemoteLoanCalculation(linkId, period, price);
				} else {
					let cacheKey = "loan-info-" + price;
					let loanInfo = spxLoanCalcEngine.loanInfoCacheMap.get(cacheKey);
					if (loanInfo) {
						if (spxLoanCalcEngine.debug) console.log("Got loanInfo from cache with key %s", cacheKey);
						spxLoanCalcEngine._updateDynamicRange(loanInfo.loanPeriods, period, price);
						spxLoanCalcEngine._callRemoteLoanCalculation(linkId, period, price, loanInfo.loanPeriods);
					} else {
						if (spxLoanCalcEngine.debug) console.log("Trying to get loanInfo from remote with key %s", cacheKey);
						spxLoanCalcEngine._callRemoteLoanInformation(linkId, period, price, cacheKey);
					}
				}
			},

			loadPageInformation: function (linkId) {
				if (!linkId) return;

				$.getJSON("https://sparxpres.dk/app/webintegration/information/", {
					linkId: linkId
				}).done(function (pageInfo) {
					if (pageInfo && pageInfo.hasOwnProperty("html")) {
						$("#sparxpresInformationPageModal .Sparxpres__dynamic-content").html(pageInfo.html);
					}
				});
			},

			_getSparxpresWebSaleElement: function () {
				spxLoanCalcEngine.$sparxpresWebSale = spxLoanCalcEngine.$sparxpresWebSale || $("#sparxpres_web_sale");
				return spxLoanCalcEngine.$sparxpresWebSale;
			},

			_callRemoteLoanCalculation: function (linkId, period, price) {
				$.getJSON("https://sparxpres.dk/app/loancalc/", {
					linkId: linkId,
					period: period,
					amount: price
				}).done(function (loanCalc) {
					if (loanCalc && loanCalc.hasOwnProperty("success")) {
						if (loanCalc.success === true) {
							if (loanCalc.termsInMonths !== period) {
								spxLoanCalcEngine._updatePeriod(loanCalc.termsInMonths);
							}
							spxLoanCalcEngine.prevCalculationPeriod = loanCalc.termsInMonths;
							spxLoanCalcEngine.prevCalculationPrice = loanCalc.loanAmount;

							$("#Sparxpres__dynamic-formattedMonthlyPayments").text(loanCalc.formattedMonthlyPayments);
							$("#Sparxpres__dynamic-complianceText").text(loanCalc.complianceText);
							$("#Sparxpres__dynamic-informationUrl").show();
						} else if (loanCalc.hasOwnProperty("errorMessage")) {
							$("#Sparxpres__dynamic-formattedMonthlyPayments").text("N/A");
							$("#Sparxpres__dynamic-complianceText").text(loanCalc.errorMessage);
							$("#Sparxpres__dynamic-informationUrl").hide();
						}
					} else {
						$("#sparxpres_web_sale").hide();
					}
				});
			},

			_callRemoteLoanInformation: function (linkId, period, price, cacheKey) {
				$.getJSON("https://sparxpres.dk/app/loaninfo/", {
					linkId: linkId,
					period: period,
					amount: price
				}).done(function (loanInfo) {
					if (loanInfo && loanInfo.hasOwnProperty("loanPeriods")) {
						spxLoanCalcEngine.loanInfoCacheMap.set(cacheKey, loanInfo);
						spxLoanCalcEngine._updateDynamicRange(loanInfo.loanPeriods, period, price);
						spxLoanCalcEngine._callRemoteLoanCalculation(linkId, period, price);
					}
				});
			},

			_updateDynamicRange: function (loanPeriods, period, price) {
				if (price === spxLoanCalcEngine.prevCalculationPrice) {
					return;
				}

				if (spxLoanCalcEngine.debug) console.log("updateSparxpresDynamicRange");
				spxLoanCalcEngine.$sliderPeriodElem = spxLoanCalcEngine.$sliderPeriodElem || $("#Sparxpres__slider");
				if (spxLoanCalcEngine.$sliderPeriodElem.length) {
					if (spxLoanCalcEngine.debug) console.log("Update slider range options...");
					spxLoanCalcEngine.$sliderPeriodElem[0].noUiSlider.updateOptions({
						start: period,
						range: {
							'min': loanPeriods[0].id,
							'max': loanPeriods[loanPeriods.length - 1].id
						},
						pips: {
							mode: 'steps',
							density: 100 / (loanPeriods.length * 2 - 2)
						}
					}, false);
				} else {
					spxLoanCalcEngine.$dropdownPeriodElem = spxLoanCalcEngine.$dropdownPeriodElem || $("#Sparxpres__dropdown select");
					if (spxLoanCalcEngine.$dropdownPeriodElem.length) {
						if (spxLoanCalcEngine.debug) console.log("Update dropdown range options");
						spxLoanCalcEngine.$dropdownPeriodElem.empty();
						$.each(loanPeriods, function (index, item) {
							spxLoanCalcEngine.$dropdownPeriodElem.append($("<option/>", {
								value: item.id,
								text: item.text
							}));
						});
						spxLoanCalcEngine.$dropdownPeriodElem.val(period);
					}
				}
			},

			_updatePeriod(period) {
				if (spxLoanCalcEngine.$dropdownPeriodElem.length) {
					if (spxLoanCalcEngine.debug) console.log("updatePeriod to: %d", period);
					spxLoanCalcEngine.$dropdownPeriodElem.val(period);
				}
			}
		};

		spxLoanCalcEngine.init();
	});

});
