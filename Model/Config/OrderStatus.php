<?php
namespace Sparxpres\Websale\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class OrderStatus implements ArrayInterface {

	public function toOptionArray() {
		return [
			['value' => 'reserved', 'label' => __('Reserved')],
			['value' => 'captured', 'label' => __('Captured')]
		];
	}

}
