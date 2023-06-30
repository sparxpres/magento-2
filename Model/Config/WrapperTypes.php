<?php
namespace Sparxpres\Websale\Model\Config;

class WrapperTypes implements \Magento\Framework\Option\ArrayInterface {

	public function toOptionArray() {
		return [
			['value' => 'simple', 'label' => __('Show the loan calculation integrated on the page')],
			['value' => 'modal', 'label' => __('Show the loan calculation in a popup window (with a button on the page)')],
            ['value' => 'none', 'label' => __('Do not show the loan calculator')],
		];
	}

}