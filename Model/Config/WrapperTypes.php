<?php
namespace Sparxpres\Websale\Model\Config;

class WrapperTypes implements \Magento\Framework\Option\ArrayInterface {

	public function toOptionArray() {
		return [
			['value' => 'simple', 'label' => __('Vis låneberegneren direkte på siden')],
			['value' => 'modal', 'label' => __('Vis låneberegneren i et modal vindue (dvs. med finansierings knap på siden)')]
		];
	}

}