<?php

namespace Kelvysmoura\Metatag\Block;

use Magento\Cms\Model\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\View\Element\Template\Context;

class Metatag extends Template
{

    /**
     * @param PageFactory $pageFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        protected PageFactory           $pageFactory,
        Context                         $context,
        array                           $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMetatagAttributes(): array
    {

        $pageId = $this->getRequest()->getParam('page_id');


        if (empty($pageId)) {
            return [];
        }

        $page = $this->pageFactory->create()->load($pageId);

        $storeIds = $this->getPageStoreIds($page);

        return array_map(function ($storeId) use ($page) {
            return $this->buildMetatagAttribute($storeId, $page->getIdentifier());
        }, $storeIds);
    }

    /**
     * @param Page $page
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPageStoreIds(Page $page)
    {
        $storeIds = (array)$page->getStoreId();

        if(count($storeIds) === 1 && $storeIds[0] == 0) {
            return $this->_storeManager->getStore()->getWebsite()->getStoreIds();
        }

        return $storeIds;
    }

    /**
     * @param int|string $storeId
     * @param string $identifier
     * @return array
     * @throws NoSuchEntityException
     */
    public function buildMetatagAttribute(int|string $storeId, string $identifier): array
    {
        $store = $this->_storeManager->getStore($storeId);

        $hreflang = $store->getConfig("general/locale/code");
        $href = $store->getBaseUrl() . $identifier;

        return [
            "hreflang" => strtolower($hreflang),
            "href" => $href,
        ];
    }
}
