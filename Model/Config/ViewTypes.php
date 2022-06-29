<?php
namespace Sparxpres\Websale\Model\Config;

class ViewTypes implements \Magento\Framework\Option\ArrayInterface {

	public function toOptionArray() {
		return [
			['value' => 'slider', 'label' => __('Slider')],
			['value' => 'dropdown', 'label' => __('Dropdown')],
            ['value' => 'plain', 'label' => __('Plain (only one period)')]
		];
	}

}