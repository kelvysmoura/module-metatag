<?php

use Magento\Cms\Model\PageFactory;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Kelvysmoura\Metatag\Block\Metatag;

class MetatagTest extends TestCase
{

    protected $pageFactoryMock;
    protected $contextMock;
    protected $storeManagerMock;
    protected $storeMock;
    protected $requestInterfaceMock;
    protected $pageMock;
    protected $websiteMock;


    protected function setUp(): void
    {
        $this->pageFactoryMock = $this->createMock(PageFactory::class);
        $this->contextMock = $this->createMock(TemplateContext::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->requestInterfaceMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->websiteMock = $this->createMock(\Magento\Store\Model\Website::class);

        $this->pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->onlyMethods(['getIdentifier', 'load'])
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetMetatagAttributesWithPageIdNull()
    {

        $this->requestInterfaceMock
            ->expects($this->once())
            ->method('getParam')
            ->with('page_id')
            ->willReturn(null);

        $this->contextMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $metatagBlockIntance = new Metatag(
            $this->pageFactoryMock,
            $this->contextMock
        );

        $result = $metatagBlockIntance->getMetatagAttributes();

        $this->assertEmpty($result);
    }

    public function testGetMetatagAttributesWithPageForAllStores()
    {

        $this->requestInterfaceMock
            ->expects($this->exactly(1))
            ->method('getParam')
            ->with($this->equalTo('page_id'))
            ->willReturn("1");

        $this->pageMock
            ->expects($this->once())
            ->method('load')
            ->with("1")
            ->willReturnSelf();

        $this->pageMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(0);

        $this->pageMock
            ->expects($this->exactly(4))
            ->method('getIdentifier')
            ->willReturn('identifier');

        $this->websiteMock
            ->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3, 4]);

        $this->storeMock
            ->expects($this->once())
            ->method('getWebsite')
            ->willReturn($this->websiteMock);

        $this->storeMock
            ->expects($this->exactly(4))
            ->method('getConfig')
            ->with('general/locale/code')
            ->willReturn('pt_br', 'en_us', 'en_gb', 'es_es');

        $this->storeMock
            ->expects($this->exactly(4))
            ->method('getBaseUrl')
            ->willReturn(
                'https://www.exemplo.com/pt-br/',
                'https://www.exemplo.com/en-us/',
                'https://www.exemplo.com/en-gb/',
                'https://www.exemplo.com/es-es/'
            );

        $this->storeManagerMock
            ->expects($this->exactly(5))
            ->method('getStore')
            ->willReturnMap([
                [null, $this->storeMock],
                [1, $this->storeMock],
                [2, $this->storeMock],
                [3, $this->storeMock],
                [4, $this->storeMock]
            ]);

        $this->pageFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->pageMock);

        $this->contextMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $this->contextMock
            ->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $metatagBlockIntance = new Metatag(
            $this->pageFactoryMock,
            $this->contextMock
        );

        $result = $metatagBlockIntance->getMetatagAttributes();

        $expected = [
            [
                'href' => 'https://www.exemplo.com/pt-br/identifier',
                'hreflang' => 'pt_br'
            ],
            [
                'href' => 'https://www.exemplo.com/en-us/identifier',
                'hreflang' => 'en_us'
            ],
            [
                'href' => 'https://www.exemplo.com/en-gb/identifier',
                'hreflang' => 'en_gb'
            ],
            [
                'href' => 'https://www.exemplo.com/es-es/identifier',
                'hreflang' => 'es_es'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetMetatagAttributesForTwoStores()
    {

        $this->requestInterfaceMock
            ->expects($this->exactly(1))
            ->method('getParam')
            ->with($this->equalTo('page_id'))
            ->willReturn("1");

        $pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->onlyMethods(['getIdentifier', 'load'])
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();

        $pageMock
            ->expects($this->once())
            ->method('load')
            ->with("1")
            ->willReturnSelf();

        $pageMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn([1,2]);

        $pageMock
            ->expects($this->exactly(2))
            ->method('getIdentifier')
            ->willReturn('identifier');

        $this->storeMock
            ->expects($this->exactly(2))
            ->method('getConfig')
            ->with('general/locale/code')
            ->willReturn('pt_br', 'en_us');

        $this->storeMock
            ->expects($this->exactly(2))
            ->method('getBaseUrl')
            ->willReturn(
                'https://www.exemplo.com/pt-br/',
                'https://www.exemplo.com/en-us/'
            );

        $this->storeManagerMock
            ->expects($this->exactly(2))
            ->method('getStore')
            ->willReturnMap([
                [1, $this->storeMock],
                [2, $this->storeMock]
            ]);

        $this->pageFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($pageMock);

        $this->contextMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $this->contextMock
            ->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $metatagBlockIntance = new Metatag(
            $this->pageFactoryMock,
            $this->contextMock
        );

        $result = $metatagBlockIntance->getMetatagAttributes();

        $expected = [
            [
                'href' => 'https://www.exemplo.com/pt-br/identifier',
                'hreflang' => 'pt_br'
            ],
            [
                'href' => 'https://www.exemplo.com/en-us/identifier',
                'hreflang' => 'en_us'
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
