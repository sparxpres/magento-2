<?php
namespace Sparxpres\Websale\Block;

abstract class SparxpresTemplate extends \Magento\Framework\View\Element\Template
{
    private static $SPARXPRES_BASE_URI = 'https://app.sparxpres.dk/spx/rest/calculator';

    protected $registry;
    private $product;
    private $linkId;
    private $loanInformation;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    abstract public function getPrice();

    abstract public function getModuleVersion();

    /**
     * @return Product
     */
    private function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');

            if (!$this->product->getId()) {
                throw new LocalizedException(__('Failed to initialize product'));
            }
        }

        return $this->product;
    }

    /**
     * @return product id
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!$this->isActive()
            || $this->getCurrencyCode() != 'DKK'
            || empty($this->getLinkId())
            || empty($this->getPrice())
            || empty($this->getLoanInformation())
        ) {
            return false;
        }
        return true;
    }

    /**
     * @return string|null
     */
    public function getLinkId()
    {
        if (is_null($this->linkId)) {
            $this->linkId = $this->_scopeConfig->getValue(
                'payment/sparxpres_gateway/link_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()->getId()
            );

            if (empty($this->linkId) || strlen($this->linkId) != 36) {
                $this->linkId = null;
            }
        }
        return $this->linkId;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $active = $this->_scopeConfig->getValue(
            'payment/sparxpres_gateway/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->_storeManager->getStore()->getId()
        );

        return !empty($active);
    }

    /**
     * @return mixed
     */
    private function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return mixed
     */
    private function getMainColor()
    {
        return $this->_scopeConfig->getValue(
            'payment/sparxpres_gateway/main_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @return mixed
     */
    private function getSliderBackgroundColor()
    {
        return $this->_scopeConfig->getValue(
            'payment/sparxpres_gateway/slider_bg_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    private function getWrapperType($isProductPage = true)
    {
        $wType = $this->_scopeConfig->getValue(
            'payment/sparxpres_gateway/display_wrapper_type_' . ($isProductPage ? 'product' : 'cart'),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
        return empty($wType) ? 'simple' : $wType;
    }

    /**
     * @return string
     */
    private function getViewType($loanPeriodCount = 0)
    {
        if ($loanPeriodCount < 2) {
            return "plain";
        }

        $vType = $this->_scopeConfig->getValue(
            'payment/sparxpres_gateway/display_view_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );

        return empty($vType) ? 'slider' : $vType;
    }

    /**
     * @return mixed
     */
    private function getLoanPeriods()
    {
        $lInfo = $this->getLoanInformation();
        if (!empty($lInfo) && property_exists($lInfo, "loanPeriods")) {
            return $lInfo->loanPeriods;
        }
        return array();
    }

    /**
     * @return int
     */
    private function getLoanPeriodCount()
    {
        return count($this->getLoanPeriods());
    }

    /**
     * @return mixed
     */
    public function getDefaultPeriod()
    {
        $lInfo = $this->getLoanInformation();
        if (!empty($lInfo) && property_exists($lInfo, "defaultPeriod")) {
            return $lInfo->defaultPeriod;
        }
        return 12;
    }

    /**
     * @return string
     */
    public function getWebSaleElementStyle()
    {
        $style = "";

        $mColor = $this->getMainColor();
        $sBgColor = $this->getSliderBackgroundColor();
        if (!empty($mColor) || !empty($sBgColor)) {
            $style .= "style=\"";
            if (!empty($mColor)) {
                $style .= "--sparxpres-main-color:".$mColor.";";
            }
            if (!empty($sBgColor)) {
                $style .= "--sparxpres-slider-bg-color:".$sBgColor.";";
            }
            $style .= "\"";
        }

        return $style;
    }

    /**
     * @return int
     */
    public function getLoanId()
    {
        $lInfo = $this->getLoanInformation();
        if (!empty($lInfo) && property_exists($lInfo, "loanId")) {
            return $lInfo->loanId;
        }
        return 0;
    }

    /**
     * @return false|mixed|string|void|null
     */
    public function getHtmlContent($isProductPage = true)
    {
        $lId = $this->getLinkId();
        $loanPeriods = $this->getLoanPeriods();

        $wrapperType = $this->getWrapperType($isProductPage);
        if ($wrapperType != 'none') {
            $viewType = $this->getViewType(count($loanPeriods));
            $html = file_get_contents(dirname(__FILE__) . '/static_html/sparxpres-' . $wrapperType . '.html');
            $html = self::get_html_with_loan_calculations(
                $lId,
                $this->getDefaultPeriod(),
                $loanPeriods,
                $viewType,
                $html
            );

            return empty($html) ? '' : $html;
        }

        return '';
    }

    /**
     * Get the loan information
     * @return mixed|null
     */
    protected function getLoanInformation()
    {
        if (is_null($this->loanInformation)) {
            $lId = $this->getLinkId();
            $price = $this->getPrice();
            if (empty($lId) || empty($price)) {
                return null;
            }

            $webSaleVersion = '';
            $version = $this->getModuleVersion();
            if (isset($version)) {
                $webSaleVersion = '&websaleversion=magento2_v' . $version;
            }

            $url = self::$SPARXPRES_BASE_URI . "/loaninfo?linkId=" . $lId . "&amount=" . $price . $webSaleVersion;
            $this->loanInformation = self::get_remote_json($url);
        }
        return $this->loanInformation;
    }

    /**
     * Get the loan calculation
     * @param $linkId
     * @param $period
     * @param $price
     * @return mixed|null
     */
    private static function getLoanCalculation($linkId, $period, $price)
    {
        if (empty($linkId) || empty($price) || empty($period)) {
            return null;
        }

        $url = self::$SPARXPRES_BASE_URI . "/loancalc?linkId=" . $linkId . "&period=" . $period . "&amount=" . $price;
        return self::get_remote_json($url);
    }

    /**
     * @param $linkId
     * @param $period
     * @param $loanPeriods
     * @param $viewType
     * @param $html
     * @return mixed|string|null
     */
    private function get_html_with_loan_calculations($linkId, $period, $loanPeriods, $viewType, $html)
    {
        $loanInformation = $this->getLoanInformation();
        $price = $this->getPrice();
        if (empty($html) || empty($loanInformation) || empty($price)) {
            return null;
        }

        $doLoanCalculation = $this->is_finance_enabled();
        $isXpresPayEnabled = $this->is_xprespay_enabled();
        if (!$doLoanCalculation && !$isXpresPayEnabled) {
            return null;
        }

        if (!$doLoanCalculation) {
            $html = "";
        } else {
            $monthlyPayments = 'N/A';
            $complianceText = 'N/A';

            $loanCalc = self::getLoanCalculation($linkId, $period, $price);
            if (isset($loanCalc) && $loanCalc->success) {
                $monthlyPayments = $loanCalc->formattedMonthlyPayments;
                $complianceText = $loanCalc->complianceText;
            } else {
                $html = "";
            }

            $periodHtml = '';
            if ($viewType == 'dropdown') {
                $periodHtml = '<select class="sparxpres-select" onchange="window.dispatchEvent('
                    .'new CustomEvent(\'sparxpresPeriodChange\', {detail: {period: this.value}}));">';
                foreach ($loanPeriods as $loanPeriod) {
                    $periodHtml .= '<option value="'
                        .$loanPeriod->id
                        .'" '
                        .($loanPeriod->id == $period ? "selected" : "")
                        .'>'
                        .$loanPeriod->text
                        .'</option>';
                }
                $periodHtml .= '</select>';
            } elseif ($viewType == 'slider') {
                $minPeriod = $loanPeriods[0]->id;
                $maxPeriod = $loanPeriods[count($loanPeriods) - 1]->id;
                $step = $loanPeriods[1]->id - $loanPeriods[0]->id;

                $style = "";
                if ($period != $minPeriod) {
                    $pct = ($period - $minPeriod) / ($maxPeriod - $minPeriod) * 100;
                    $style = "style=\"--sparxpres-slider-pct:" . round($pct, 2) . "%;\"";
                }

                $periodHtml = '<input type="range" class="sparxpres-slider" prefix="mdr." '
                    .'min="'.$minPeriod.'" '
                    .'max="'.$maxPeriod.'" '
                    .'step="'.$step.'" '
                    .'value="'.$period.'" '
                    .'data-validate="{rules: {range: [1,200]}}" '
                    .'onchange="window.dispatchEvent('
                    .  'new CustomEvent(\'sparxpresPeriodChange\', {detail: {period: this.value}}));" '
                    .'oninput="window.dispatchEvent('
                    .  'new CustomEvent(\'sparxpresPeriodInput\', {detail: {period: this.value, '
                    .    'min: this.getAttribute(\'min\'), max: this.getAttribute(\'max\')}}));" '
                    .$style
                    .' />';

                $periodHtml .= '<div class="sparxpres-slider-steps">';
                foreach ($loanPeriods as $loanPeriod) {
                    $periodHtml .= '<div class="sparxpres-slider-step">' . $loanPeriod->id . '</div>';
                }
                $periodHtml .= '</div>';
            }

            if (!empty($periodHtml)) {
                $periodHtml = '<div id="sparxpres_web_sale_period">' . $periodHtml . '</div>';
            }

            $html = str_replace('##PERIOD_HTML##', $periodHtml, $html);
            $html = str_replace('##MONTHLY_PAYMENTS##', $monthlyPayments, $html);
            $html = str_replace('##COMPLIANCE_TEXT##', $complianceText, $html);
        }

        if ($isXpresPayEnabled) {
            $html .= file_get_contents(dirname(__FILE__) . '/static_html/xprespay.html');
        }

        return $html;
    }

    /**
     * @return bool
     */
    public function is_finance_enabled()
    {
        $loanInformation = $this->getLoanInformation();
        $price = $this->getPrice();

        return isset($loanInformation)
            && $loanInformation->loanId > 0
            && !empty($price)
            && $price >= $loanInformation->minAmount
            && $price <= $loanInformation->maxAmount;
    }

    /**
     * Is credit enabled?
     * @return bool
     */
    public function is_xprespay_enabled()
    {
        $loanInformation = $this->getLoanInformation();
        $price = $this->getPrice();

        return isset($loanInformation)
            && !empty($price)
            && $loanInformation->spxCreditEnabled
            && $price <= $loanInformation->spxCreditMaximum;
    }

    /**
     * Get json from url and return it as an object
     * @param $url
     * @return mixed|null
     */
    private static function get_remote_json($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 4);    // Connection timeout
        curl_setopt($curl, CURLOPT_TIMEOUT, 6);           // Total timeout incl. connection timeout
        $data = curl_exec($curl);
        $errno = curl_errno($curl);
        curl_close($curl);

        return $errno ? null : json_decode($data);
    }
}
