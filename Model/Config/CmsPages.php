<?php
namespace Sparxpres\Websale\Model\Config;

class CmsPages implements \Magento\Framework\Option\ArrayInterface {
	private $_cmsPage;
	private $_search;

	public function __construct(
		\Magento\Cms\Api\PageRepositoryInterface $pageRepository,
		\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
	) {
		$this->_cmsPage = $pageRepository;
		$this->_search = $searchCriteriaBuilder;
	}

	public function toOptionArray() {
		$pages = [];

        $pages[] = [
            'value' => '-1',
            'label' => 'Standard: Modal (åbner ovenpå siden)'
        ];

        foreach ($this->_cmsPage->getList($this->_getSearchCriteria())->getItems() as $page) {
			$pages[] = [
				'value' => $page->getId(),
				'label' => $page->getTitle()
			];
		}
		return $pages;
	}

	protected function _getSearchCriteria() {
		return $this->_search->addFilter('is_active', '1')->create();
	}
}