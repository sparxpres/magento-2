<?php
namespace Sparxpres\Websale\Block;

class Cart extends SparxpresTemplate
{
    private $price;
    private $moduleList;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    )
    {
        $this->moduleList = $moduleList;
        parent::__construct($context, $objectManager, $data);
    }

    public function getModuleVersion()
    {
        $moduleInfo = $this->moduleList->getOne('Sparxpres_Websale');
        return $moduleInfo['setup_version'];
    }

    public function getPrice()
    {
        if (is_null($this->price)) {
            $cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
            $this->price = ceil($cart->getQuote()->getGrandTotal());
        }
        return $this->price;
    }

    public function getContent() {
        return parent::getHtmlContent(false);
    }
}
