<?php
namespace Sparxpres\Websale\Block;

class Product extends SparxpresTemplate {
	protected $_helper;
	private $_price;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Helper\Data $helper,
		array $data = []
	) {
		$this->_helper = $helper;
		parent::__construct($context, $objectManager, $data);
	}

	protected function getModuleVersion() {
		return null;	// Don't send module version on product pages
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
			$product = $this->_helper->getProduct();
			if (!$product->getId()) {
				throw new LocalizedException(__('Failed to initialize product'));
			}
			$this->_price = ceil($product->getPrice());
		}
		return $this->_price;
	}

}
