<?php
namespace Sparxpres\Websale\Block;

class Cart extends SparxpresTemplate {
	private $_price;
    private $_moduleList;

    public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
		array $data = []
	) {
        $this->_moduleList = $moduleList;
		parent::__construct($context, $objectManager, $data);
	}

	public function getModuleVersion() {
        $moduleInfo = $this->_moduleList->getOne('Sparxpres_Websale');
        return $moduleInfo['setup_version'];
	}

	public function isValid() {
		return parent::isValid();
	}

	public function getLinkId() {
		return parent::getLinkId();
	}

	public function getDisplayContent() {
		return parent::getDisplayContent();
	}

    public function getDefaultPeriod() {
        return parent::getDefaultPeriod();
    }

    public function isDynamicPeriod() {
        return parent::isDynamicPeriod();
    }

    public function getPrice() {
		if (is_null($this->_price)) {
			$cart = $this->_objectManager->get('\Magento\Checkout\Model\Cart');
			$this->_price = ceil($cart->getQuote()->getGrandTotal());
		}
		return $this->_price;
	}

}
