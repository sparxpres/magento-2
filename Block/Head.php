<?php
namespace Sparxpres\Websale\Block;

class Head extends \Magento\Framework\View\Element\Template {
    protected $assetRepository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->assetRepository = $context->getAssetRepository();
        parent::__construct($context, $data);
    }

    public function getSparxpresCSS() {
        $asset = $this->assetRepository->createAsset('Sparxpres_Websale::css/sparxpres-websale.css');
        return $asset->getUrl();
    }

    public function getSparxpresSliderCSS() {
        $asset = $this->assetRepository->createAsset('Sparxpres_Websale::css/sparxpres-websale-slider.css');
        return $asset->getUrl();
    }
}
