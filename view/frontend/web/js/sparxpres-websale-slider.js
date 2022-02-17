require(['noUiSlider'],function(noUiSlider){

	/**
	 * Init the loan calculation, called from image tag's onload
	 * @param element
	 * @param retryCnt
	 */
	function sparxpresSliderInit(element, retryCnt=1) {

		if (element && element.dataset) {
			let periods = JSON.parse(element.dataset.periods);
			let defaultPeriod = element.dataset.defaultPeriod;
			let step = parseInt(periods[1].id) - parseInt(periods[0].id);
			let periodSliderElem = document.getElementById("Sparxpres__slider");

			noUiSlider.create(periodSliderElem, {
				start: defaultPeriod,
				step: step,
				connect: 'lower',
				cssPrefix: 'spxUi-',
				range: {
					'min': periods[0].id,
					'max': periods[periods.length-1].id
				},
				pips: {
					mode: 'steps',
					density: 100/(periods.length*2-2)
				}
			});

			periodSliderElem.noUiSlider.on('set', function (values, handle) {
				window.dispatchEvent(new CustomEvent("sparxpresSliderRuntimeChange", {detail: {period: values[handle]}}));
			});
		} else {
			if (retryCnt < 5) {
				console.log("Sparxpres slider initialize retrying: ", retryCnt);
				setTimeout(function() {sparxpresSliderInit(document.getElementById("sparxpres_web_sale_init"),retryCnt+1);},200*retryCnt);
			}
		}
	}

	sparxpresSliderInit(document.getElementById("sparxpres_web_sale_init"));
});
