<?php
namespace Sparxpres\Websale\Block;

class Cart extends SparxpresTemplate
{
    private $checkoutSession;
    private $price;
    private $moduleList;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    )
    {
        $this->moduleList = $moduleList;
        $this->checkoutSession = $session;
        parent::__construct($context, $registry, $data);
    }

    public function getModuleVersion()
    {
        $moduleInfo = $this->moduleList->getOne('Sparxpres_Websale');
        return $moduleInfo['setup_version'];
    }

    public function getPrice()
    {
        if (is_null($this->price)) {
            $this->price = ceil($this->checkoutSession->getQuote()->getGrandTotal());
        }
        return $this->price;
    }

    public function getContent()
    {
        return parent::getHtmlContent(false);
    }
}
