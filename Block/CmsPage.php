<?php
namespace Sparxpres\Websale\Block;

class CmsPage extends SparxpresTemplate {
	private $_page;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Cms\Model\Page $page,
		array $data = []
	) {
		$this->_page = $page;
		parent::__construct($context, $objectManager, $data);
	}

	protected function getModuleVersion() {
		return null;	// Don't send module version on information page
	}

	public function isValid() {
		if (empty($this->getLinkId()) || empty($this->getInformationPageId())) {
			return false;
		}

		$pageId = $this->_page->getId();
		if ($pageId === $this->getInformationPageId()) {
			return true;
		}

		return false;
	}

	public function getPrice() {
		return 0;
	}

	public function getCmsPageInfoContent() {
		return $this->getInformationPageContent();
	}

}
