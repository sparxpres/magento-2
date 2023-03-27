<?php
namespace Sparxpres\Websale\Block;

class Product extends SparxpresTemplate
{
    protected $helper;
    private $price;
    private $moduleList;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $helper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->moduleList = $moduleList;
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
            $product = $this->helper->getProduct();
            if (!$product->getId()) {
                throw new LocalizedException(__('Failed to initialize product'));
            }
            $this->price = ceil($this->helper->getTaxPrice($product, $product->getFinalPrice(), true));
        }
        return $this->price;
    }

    public function getContent()
    {
        return parent::getHtmlContent(true);
    }

}
