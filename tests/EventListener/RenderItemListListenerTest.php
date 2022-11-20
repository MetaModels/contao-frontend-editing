<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Mini Model <minimodel@metamodel.me>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\Test\EventListener;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\System;
use Contao\TemplateLoader;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\DcGeneral\Cache\Factory\DcGeneralFactoryCache;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\FrontendEditor;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface as CcaTranslatorInterface;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use MetaModels\ContaoFrontendEditingBundle\EventListener\RenderItemListListener;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\IItem;
use MetaModels\MetaModelsEvents;
use MetaModels\Render\Setting\Collection;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Template;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use MetaModels\IMetaModel;
use MetaModels\ItemList;
use MetaModels\FrontendIntegration\HybridList;
use MetaModels\ContaoFrontendEditingBundle\FrontendEditHybrid;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This tests the RenderItemListListener.
 *
 * @covers \MetaModels\ContaoFrontendEditingBundle\EventListener\RenderItemListListener
 */
class RenderItemListListenerTest extends TestCase
{
    /**
     * This is the hack to mimic the Contao auto loader.
     *
     * @return void
     */
    public static function contaoAutoload($class): void
    {
        if (0 === strpos($class, 'Contao\\')) {
            return;
        }
        $result = class_exists('Contao\\' . $class);

        if ($result) {
            class_alias('Contao\\' . $class, $class);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        // This is the hack to mimic the Contao auto loader.
        spl_autoload_register(self::class . '::contaoAutoload');
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        spl_autoload_unregister(self::class . '::contaoAutoload');
        parent::tearDown();
    }

    /**
     * Test that the method works correctly.
     *
     * @return void
     */
    public function testHandleForItemRenderingDoesNothingWithoutEditFlag(): void
    {
        $renderSettings  = $this->getMockForAbstractClass(ICollection::class);
        $item            = $this->getMockForAbstractClass(IItem::class);
        $translator      = $this->getMockForAbstractClass(TranslatorInterface::class);
        $ccaTranslator   = $this->getMockForAbstractClass(CcaTranslatorInterface::class);
        $factory         = $this->getMockForAbstractClass(IFactory::class);
        $eventDispatcher = new EventDispatcher();
        $frontendEditor  = new FrontendEditor($eventDispatcher, $ccaTranslator);

        /** @var ICollection $renderSettings */
        /** @var IItem $item */

        $event    = new ParseItemEvent($renderSettings, $item, 'html5', []);
        $listener = new RenderItemListListener($translator, $eventDispatcher, $factory, $frontendEditor);

        $listener->handleForItemRendering($event);

        $this->assertEquals([], $event->getResult());
    }

    /**
     * Test that the method works correctly.
     *
     * @return void
     */
    public function testHandleForItemRenderingAddsWithEditFlag(): void
    {
        $container   = $this->getMockForAbstractClass(ContainerInterface::class);
        $definitions = $this->getMockForAbstractClass(DataDefinitionContainerInterface::class);
        System::setContainer($container);

        $dcGeneralFactoryCache = $this->getMockBuilder(CacheProvider::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $container
            ->method('get')
            ->withConsecutive(
                [
                    DcGeneralFactoryCache::class
                ],
                [
                    'cca.dc-general.data-definition-container'
                ],
                [
                    DcGeneralFactoryCache::class
                ]
            )
            ->willReturn($dcGeneralFactoryCache, $definitions, $dcGeneralFactoryCache);

        $basicDefinition = $this->getMockForAbstractClass(BasicDefinitionInterface::class);
        $basicDefinition->method('isEditable')->willReturn(true);

        $definition = $this->getMockForAbstractClass(
            \ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface::class
        );
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);

        $definitions->method('hasDefinition')->with('mm_test')->willReturn(true);
        $definitions->method('getDefinition')->with('mm_test')->willReturn($definition);

        $metaModel      = $this->getMockForAbstractClass(IMetaModel::class);
        $renderSettings = $this->getMockForAbstractClass(ICollection::class);
        $item           = $this->getMockForAbstractClass(IItem::class);
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $ccaTranslator  = $this->getMockForAbstractClass(CcaTranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $dispatcher     = new EventDispatcher();
        $frontendEditor = new FrontendEditor($dispatcher, $ccaTranslator);

        $translator->expects($this->once())->method('trans')->willReturn('');

        $metaModel->expects($this->any())->method('getTableName')->willReturn('mm_test');
        $item
            ->expects($this->any())
            ->method('getMetaModel')
            ->willReturn($metaModel);
        $item
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($name) {
                    switch ($name) {
                        case 'id':
                            return 'item-id';
                        default:
                    }
                    return null;
                }
            );

        $renderSettings
            ->expects($this->any())
            ->method('get')
            ->with()
            ->willReturnCallback(
                function ($name) {
                    switch ($name) {
                        case RenderItemListListener::FRONTEND_EDITING_ENABLED_FLAG:
                            return true;
                        case RenderItemListListener::FRONTEND_EDITING_PAGE:
                            return ['id' => 11, 'language' => 'en', 'alias' => 'test-page'];
                        default:
                    }
                    return null;
                }
            );

        /** @var ICollection $renderSettings */
        /** @var IItem $item */

        $event    = new ParseItemEvent($renderSettings, $item, 'html5', []);
        $listener = new RenderItemListListener($translator, $dispatcher, $factory, $frontendEditor);

        $listener->handleForItemRendering($event, MetaModelsEvents::PARSE_ITEM, $dispatcher);

        $result = $event->getResult();
        $this->assertArrayHasKey('edit', $result['actions']);
        $this->assertEquals(
            'act=edit&id=' . ModelId::fromValues('mm_test', 'item-id')->getSerialized(),
            $result['actions']['edit']['href']
        );
    }

    /**
     * Test that the method works correctly.
     *
     * @return void
     */
    public function testFrontendEditingInListRenderingDoesNothingForInvalidCaller(): void
    {
        $framework = $this->getMockBuilder(ContaoFramework::class)
            ->disableOriginalConstructor()
            ->getMock();
        $framework
            ->expects(self::once())
            ->method('getAdapter')
            ->with(TemplateLoader::class)
            ->willReturn(new Adapter(TemplateLoader::class));

        $scopeDeterminator = $this->getMockBuilder(RequestScopeDeterminator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container
            ->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                [
                    'contao.framework'
                ],
                [
                    'cca.dc-general.scope-matcher'
                ]
            )
            ->willReturn($framework, $scopeDeterminator);
        System::setContainer($container);

        $itemList       = $this->createMock(ItemList::class);
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $ccaTranslator  = $this->getMockForAbstractClass(CcaTranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $dispatcher     = new EventDispatcher();
        $frontendEditor = new FrontendEditor($dispatcher, $ccaTranslator);
        $template       = new Template();
        $event          = new RenderItemListEvent($itemList, $template, new \DateTime());
        $listener       = new RenderItemListListener($translator, $dispatcher, $factory, $frontendEditor);

        $listener->handleFrontendEditingInListRendering($event);

        $this->assertEquals(null, $template->editEnable);
    }

    /**
     * Test that the method works correctly.
     *
     * @return void
     */
    public function testFrontendEditingInListRenderingSetFlagsWithEditFlagBeingFalse(): void
    {
        $framework = $this->getMockBuilder(ContaoFramework::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $framework
            ->expects(self::once())
            ->method('getAdapter')
            ->with(TemplateLoader::class)
            ->willReturn(new Adapter(TemplateLoader::class));

        $scopeDeterminator = $this->getMockBuilder(RequestScopeDeterminator::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container
            ->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                [
                    'contao.framework'
                ],
                [
                    'cca.dc-general.scope-matcher'
                ]
            )
            ->willReturn($framework, $scopeDeterminator);
        System::setContainer($container);

        $renderSettings = $this->getMockForAbstractClass(ICollection::class);
        $dispatcher     = new EventDispatcher();
        $itemList       = $this->createMock(ItemList::class);
        $itemListModel  = $this->createMock(Model::class);
        $template       = new Template();
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $ccaTranslator  = $this->getMockForAbstractClass(CcaTranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor = new FrontendEditor($dispatcher, $ccaTranslator);
        $caller         = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $caller->Template = new \stdClass();

        $itemList
            ->expects($this->any())
            ->method('getView')
            ->willReturn($renderSettings);
        $itemList
            ->expects($this->any())
            ->method('getModel')
            ->willReturn($itemListModel);

        $event    = new RenderItemListEvent($itemList, $template, $caller);
        $listener = new RenderItemListListener($translator, $dispatcher, $factory, $frontendEditor);

        $listener->handleFrontendEditingInListRendering($event);

        $this->assertEquals(false, $template->editEnable);
    }

    /**
     * Test that the compile method works correctly.
     *
     * @return void
     */
    public function testFrontendEditingInListRenderingRevertsWithoutPage(): void
    {
        $framework = $this->getMockBuilder(ContaoFramework::class)
            ->disableOriginalConstructor()
            ->getMock();
        $framework
            ->expects(self::once())
            ->method('getAdapter')
            ->with(TemplateLoader::class)
            ->willReturn(new Adapter(TemplateLoader::class));

        $scopeDeterminator = $this->getMockBuilder(RequestScopeDeterminator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container
            ->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                [
                    'contao.framework'
                ],
                [
                    'cca.dc-general.scope-matcher'
                ]
            )
            ->willReturn($framework, $scopeDeterminator);
        System::setContainer($container);

        $dispatcher       = new EventDispatcher();
        $metaModel        = $this->getMockForAbstractClass(IMetaModel::class);
        $translator       = $this->getMockForAbstractClass(TranslatorInterface::class);
        $ccaTranslator    = $this->getMockForAbstractClass(CcaTranslatorInterface::class);
        $filterFactory    = $this->getMockForAbstractClass(IFilterSettingFactory::class);
        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)->disableOriginalConstructor()->getMock();
        $renderSettings   = new Collection($metaModel, [], $dispatcher, $filterFactory, $filterUrlBuilder);
        $factory          = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor   = new FrontendEditor($dispatcher, $ccaTranslator);
        $itemList         = $this->createMock(ItemList::class);
        $template         = new Template();
        $caller           = $this
            ->getMockBuilder(FrontendEditHybrid::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $caller->Template             = new \stdClass();
        $caller->metamodel_fe_editing = true;

        $itemList
            ->expects($this->any())
            ->method('getView')
            ->willReturn($renderSettings);

        $metaModel->expects($this->any())->method('getTableName')->willReturn('mm_test');

        $event    = new RenderItemListEvent($itemList, $template, $caller);
        $listener = new RenderItemListListener($translator, $dispatcher, $factory, $frontendEditor);

        $listener->handleFrontendEditingInListRendering($event);

        $this->assertEquals(false, $template->editEnable);
    }

    /**
     * Test that the compile method works correctly.
     *
     * @return void
     */
    public function testFrontendEditingInListRenderingRevertsWithoutPageDetails(): void
    {
        $framework = $this->getMockBuilder(ContaoFramework::class)
            ->disableOriginalConstructor()
            ->getMock();
        $framework
            ->expects(self::once())
            ->method('getAdapter')
            ->with(TemplateLoader::class)
            ->willReturn(new Adapter(TemplateLoader::class));

        $scopeDeterminator = $this->getMockBuilder(RequestScopeDeterminator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container
            ->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                [
                    'contao.framework'
                ],
                [
                    'cca.dc-general.scope-matcher'
                ]
            )
            ->willReturn($framework, $scopeDeterminator);
        System::setContainer($container);

        $metaModel        = $this->getMockForAbstractClass(IMetaModel::class);
        $dispatcher       = new EventDispatcher();
        $filterFactory    = $this->getMockForAbstractClass(IFilterSettingFactory::class);
        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)->disableOriginalConstructor()->getMock();
        $renderSettings   = new Collection($metaModel, [], $dispatcher, $filterFactory, $filterUrlBuilder);
        $itemList         = $this->createMock(ItemList::class);
        $translator       = $this->getMockForAbstractClass(TranslatorInterface::class);
        $ccaTranslator    = $this->getMockForAbstractClass(CcaTranslatorInterface::class);
        $factory          = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor   = new FrontendEditor($dispatcher, $ccaTranslator);
        $template         = new Template();
        $caller           = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $caller->Template                  = new \stdClass();
        $caller->metamodel_fe_editing      = true;
        $caller->metamodel_fe_editing_page = 15;

        $itemList
            ->expects($this->any())
            ->method('getView')
            ->willReturn($renderSettings);

        $metaModel->expects($this->any())->method('getTableName')->willReturn('mm_test');

        $event    = new RenderItemListEvent($itemList, $template, $caller);
        $listener = new RenderItemListListener($translator, $dispatcher, $factory, $frontendEditor);

        $listener->handleFrontendEditingInListRendering($event);

        $this->assertEquals(false, $template->editEnable);
    }

    /**
     * Test that the compile method works correctly.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testFrontendEditingInListRenderingAddsWithEditFlag(): void
    {
        $reflection = new \ReflectionProperty(FrontendEditor::class, 'environments');
        $reflection->setAccessible(true);
        $reflection->setValue([]);

        $framework = $this->getMockBuilder(ContaoFramework::class)
            ->disableOriginalConstructor()
            ->getMock();
        $framework
            ->expects(self::once())
            ->method('getAdapter')
            ->with(TemplateLoader::class)
            ->willReturn(new Adapter(TemplateLoader::class));

        $scopeDeterminator = $this->getMockBuilder(RequestScopeDeterminator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dcGeneralFactoryCache = $this->getMockBuilder(CacheProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container   = $this->getMockForAbstractClass(ContainerInterface::class);
        $definitions = $this->getMockForAbstractClass(DataDefinitionContainerInterface::class);
        System::setContainer($container);
        $container
            ->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(
                [
                    'contao.framework'
                ],
                [
                    'cca.dc-general.scope-matcher'
                ],
                [
                    DcGeneralFactoryCache::class
                ],
                [
                    'cca.dc-general.data-definition-container'
                ]
            )
            ->willReturn(
                $framework,
                $scopeDeterminator,
                $dcGeneralFactoryCache,
                $definitions
            );

        $basicDefinition = $this->getMockForAbstractClass(BasicDefinitionInterface::class);
        $basicDefinition->expects($this->once())->method('isCreatable')->willReturn(true);

        $definition = $this->getMockForAbstractClass(
            \ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface::class
        );
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);

        $definitions->method('hasDefinition')->with('mm_test')->willReturn(true);
        $definitions->method('getDefinition')->with('mm_test')->willReturn($definition);

        $metaModel        = $this->getMockForAbstractClass(IMetaModel::class);
        $dispatcher       = new EventDispatcher();
        $filterFactory    = $this->getMockForAbstractClass(IFilterSettingFactory::class);
        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)->disableOriginalConstructor()->getMock();
        $renderSettings   = new Collection($metaModel, [], $dispatcher, $filterFactory, $filterUrlBuilder);
        $itemList         = $this->createMock(ItemList::class);
        $itemListModel    = $this->createMock(Model::class);
        $translator       = $this->getMockForAbstractClass(TranslatorInterface::class);
        $ccaTranslator    = $this->getMockForAbstractClass(CcaTranslatorInterface::class);
        $factory          = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor   = new FrontendEditor($dispatcher, $ccaTranslator);
        $template         = new Template();
        $caller           = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $caller->Template                  = new \stdClass();
        $caller->metamodel_fe_editing      = true;
        $caller->metamodel_fe_editing_page = 15;
        $caller->metamodel                 = 10;

        $itemList
            ->expects($this->any())
            ->method('getView')
            ->willReturn($renderSettings);
        $itemList
            ->expects($this->any())
            ->method('getModel')
            ->willReturn($itemListModel);
        $itemListModel
            ->expects($this->any())
            ->method('__get')
            ->withConsecutive(
                [
                    'metamodel_fe_editing'
                ],
                [
                    'metamodel_fe_editing_page'
                ],
                [
                    'metamodel'
                ]
            )
            ->willReturn(1, 15, 10);

        $factory->expects($this->once())->method('translateIdToMetaModelName')->with(10)->willReturn('mm_test');

        $metaModel->expects($this->any())->method('getTableName')->willReturn('mm_test');

        $dispatcher->addListener(
            ContaoEvents::CONTROLLER_GET_PAGE_DETAILS,
            function (GetPageDetailsEvent $event) {
                if (15 === $event->getPageId()) {
                    $event->setPageDetails(['language' => 'en', 'alias' => 'test-page']);
                }
            }
        );

        $event    = new RenderItemListEvent($itemList, $template, $caller);
        $listener = new RenderItemListListener($translator, $dispatcher, $factory, $frontendEditor);

        $listener->handleFrontendEditingInListRendering($event);

        $this->assertEquals(true, $template->editEnable);
    }
}
