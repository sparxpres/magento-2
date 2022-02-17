<?php
namespace Sparxpres\Websale\Block;

abstract class SparxpresTemplate extends \Magento\Framework\View\Element\Template {
	private static $SPARXPRES_BASE_URI = 'https://app.sparxpres.dk/spx/';

	protected $_objectManager;
	private $_linkId;
	private $_loanInformation;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = []
	) {
		$this->_objectManager = $objectManager;
		parent::__construct($context, $data);
	}

	abstract public function getPrice();

	abstract protected function getModuleVersion();

	/**
	 * @return bool
	 */
	protected function isValid() {
		if ($this->getCurrencyCode() !== 'DKK') {
			return false;
		} else if (empty($this->getLinkId())) {
			return false;
		} else if (empty($this->getLoanInformation(is_null($this->getModuleVersion()) ? null : $this->getModuleVersion()))) {
			return false;
		} else if (empty($this->getPrice())) {
			return false;
		} else if ($this->getPrice() < $this->getLoanInformation()->minAmount || $this->getPrice() > $this->getLoanInformation()->maxAmount) {
			return false;
		}
		return true;
	}

    /**
	 * @return string|null
	 */
	protected function getLinkId() {
		if (is_null($this->_linkId)) {
			$this->_linkId = $this->_scopeConfig->getValue('sparxpres/general/link_id');
			if (empty($this->_linkId) || strlen($this->_linkId) !== 36) {
				$this->_linkId = null;
			}
		}
		return $this->_linkId;
	}

	/**
	 * @return string
	 */
	protected function getDisplayContent() {
		// Build the content return string
		$content = '<div class="SparxpresDisplayControl">' . $this->getHtmlContent() . '</div>';

		if ($this->isUseSlider()) {
			$content .= '<div style="display: none;">';
			$content .= '<svg id="sparxpres_web_sale_init" width="1" height="1"';
			$content .= ' data-default-period="' . $this->getDefaultPeriod() . '"';
			$content .= ' data-periods="' . htmlentities(json_encode($this->getLoanPeriods()), ENT_COMPAT) . '"';
			$content .= '></svg>';
			$content .= '</div>';
		}

		// Should we change main color?
		$mColor = $this->getMainColor();
		if (!empty($mColor)) {
			$content .= '<style>';
            $content .= '.Sparxpres__main-color {color: ' . $mColor . ';}';
            $content .= '.Sparxpres__modal-open {background-color: ' . $mColor . ';}';
            $content .= '.SparxpresSlider .spxUi-connect, .SparxpresSlider .spxUi-handle .spxUi-touch-area {color: ' . $mColor . '; background: ' . $mColor . ';}';
			$content .= '</style>';
		}
		return $content;
	}

	/**
	 * Get the loan information text from sparxpres
	 * @return string|void
	 */
	protected function getInformationPageContent() {
		$url = self::$SPARXPRES_BASE_URI . "webintegration/content?wrapper=simple&type=information&linkId=" . $this->getLinkId();
		$data = $this->get_remote_json($url);
		if (empty($data)) {
			return;
		}

		return '<div id="sparxpres_web_sale_info" class="sparxpres_information">' . $data->html . '</div>';
	}

	/**
	 * @return mixed
	 */
	private function getCurrencyCode() {
		return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
	}

	/**
	 * @return mixed
	 */
	private function getMainColor() {
		return $this->_scopeConfig->getValue('sparxpres/general/main_color');
	}

	/**
	 * @return mixed
	 */
	protected function getInformationPageId() {
		return $this->_scopeConfig->getValue('sparxpres/general/info_page_id');
	}

	/**
	 * @return string
	 */
	private function getWrapperType() {
		$wType = $this->_scopeConfig->getValue('sparxpres/general/display_wrapper_type_product');
		return empty($wType) ? 'simple' : $wType;
	}

	/**
	 * @return string
	 */
	private function getViewType($loanPeriodCount = 0) {
        if ($loanPeriodCount === 0) $loanPeriodCount = $this->getLoanPeriodCount();
        if ($loanPeriodCount <= 1 || preg_match('/(msie|trident)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return "plain";
        }

		$vType = $this->_scopeConfig->getValue('sparxpres/general/display_view_type');
		return empty($vType) ? 'slider' : $vType;
	}

    /**
     * @return bool
     */
    protected function isUseSlider() {
        return $this->getViewType() === 'slider';
    }

	/**
	 * @return mixed
	 */
	private function getLoanPeriods() {
		$lInfo = $this->getLoanInformation();
		if (!empty($lInfo)) {
			return $lInfo->loanPeriods;
		}
        return array();
	}

	/**
	 * @return int
	 */
	private function getLoanPeriodCount() {
		return count($this->getLoanPeriods());
	}

	/**
	 * @return mixed
	 */
	protected function getDefaultPeriod() {
		$lPeriods = $this->getLoanPeriods();
		if (!empty($lPeriods)) {
			$_price = $this->getPrice();
			$_defaultRowIdx = ceil(count($lPeriods)/2)-1;
			while ($_defaultRowIdx > 0 && floor($_price / $lPeriods[$_defaultRowIdx]->id) < 100) {
				$_defaultRowIdx -= 1;
			}
			return $lPeriods[$_defaultRowIdx]->id;
		}
	}

    /**
     * @return false|mixed
     */
    protected function isDynamicPeriod() {
        $lInfo = $this->getLoanInformation();
        if (!empty($lInfo)) {
            return $lInfo->dynamicPeriod;
        }
        return false;
    }

	/**
	 * @return false|mixed|string|void|null
	 */
	private function getHtmlContent() {
		$linkId = $this->getLinkId();
		$price = $this->getPrice();
		$loanPeriods = $this->getLoanPeriods();
		$defaultPeriod = $this->getDefaultPeriod();

		$pageId = $this->getInformationPageId();
        $infoPageUrl = $pageId > 0 ? $this->_objectManager->create('\Magento\Cms\Helper\Page')->getPageUrl($pageId) : null;

		$wrapperType = $this->getWrapperType();
		$viewType = $this->getViewType(count($loanPeriods));
		$html = file_get_contents( dirname(__FILE__) . '/static_html/sparxpres-'.$wrapperType.'-'.$viewType.'.html');
		$html = self::get_html_with_loan_calculations($linkId, $defaultPeriod, $loanPeriods, $viewType, $price, $html, $infoPageUrl);
		if (empty($html)) {
			return;
		}

		return $html;
	}

	/**
	 * Get the loan information
	 * @param $version
	 * @return mixed|null
	 */
	private function getLoanInformation($version = null) {
		if (is_null($this->_loanInformation)) {
			$linkId = $this->getLinkId();
			$price = $this->getPrice();
			if (empty($linkId) || empty($price)) {
				return null;
			}

			$webSaleVersion = '';
			if (isset($version)) {
				$webSaleVersion = '&websaleversion=magento_v' . $version;
			}

			$url = self::$SPARXPRES_BASE_URI . "loaninfo?linkId=" . $linkId . "&amount=" . $price . $webSaleVersion;
			$this->_loanInformation = self::get_remote_json($url);
		}
		return $this->_loanInformation;
	}

	/**
	 * Get the loan calculation
	 * @param $linkId
	 * @param $period
	 * @param $price
	 * @return mixed|null
	 */
	private static function getLoanCalculation($linkId, $period, $price) {
		if (empty($linkId) || empty($price) || empty($period)) {
			return null;
		}

		$url = self::$SPARXPRES_BASE_URI . "loancalc?linkId=" . $linkId . "&period=" . $period . "&amount=" . $price;
		return self::get_remote_json($url);
	}

	/**
	 * @param $linkId
	 * @param $period
	 * @param $loanPeriods
	 * @param $viewType
	 * @param $price
	 * @param $html
	 * @param $informationUrl
	 * @return mixed|string|null
	 */
	private static function get_html_with_loan_calculations($linkId, $period, $loanPeriods, $viewType, $price, $html, $informationUrl) {
		if (empty($html)) {
			return null;
		}

		$_monthlyPayments = 'N/A';
		$_complianceText = 'N/A';
		$loanCalc = self::getLoanCalculation($linkId, $period, $price);
        if (!empty($loanCalc) && !empty($loanCalc->success) && $loanCalc->success) {
			$_monthlyPayments = $loanCalc->formattedMonthlyPayments;
			$_complianceText = $loanCalc->complianceText;
		}

		if ($viewType === 'dropdown') {
            $optionsHtml = '<select id="Sparxpres__dropdown_period_selection" class="Sparxpres__select-css" onchange="window.dispatchEvent(new CustomEvent(\'sparxpresSelectRuntimeChange\', {detail: {period: this.value}}));">';
			foreach ($loanPeriods as $loanPeriod) {
				$optionsHtml .= '<option value="' . $loanPeriod->id . '" ' . ($loanPeriod->id === $period ? "selected" : "") . '>' . $loanPeriod->text . '</option>';
			}
			$optionsHtml .= '</select>';

			$html = str_replace('##PERIOD_OPTIONS##', $optionsHtml, $html);
		}

		$html = str_replace('##MONTHLY_PAYMENTS##', $_monthlyPayments, $html);
		$html = str_replace('##COMPLIANCE_TEXT##', $_complianceText, $html);
        if (empty($informationUrl)) {
            $html = str_replace('"##INFORMATION_URL##"', '"javascript:void(0);" onclick="window.dispatchEvent(new Event(\'sparxpresInformationPageOpen\'));"', $html);
        } else {
            $html = str_replace('##INFORMATION_URL##', $informationUrl, $html);
        }
		return $html;
	}

	/**
	 * Get json from url and return it as an object
	 * @param $url
	 * @return mixed|null
	 */
	private static function get_remote_json($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 4);	// Connection timeout
		curl_setopt($curl, CURLOPT_TIMEOUT, 6);			// Total timeout incl. connection timeout
		$data = curl_exec($curl);
		$errno = curl_errno($curl);
		curl_close($curl);

		return $errno ? null : json_decode($data);
	}
}
