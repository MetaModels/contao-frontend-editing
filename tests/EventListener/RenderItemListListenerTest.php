<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\Test\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\FrontendEditor;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use MetaModels\ContaoFrontendEditingBundle\EventListener\RenderItemListListener;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\IFactory;
use MetaModels\IItem;
use MetaModels\MetaModelsEvents;
use MetaModels\Render\Setting\Collection;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Template;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Translation\TranslatorInterface;
use MetaModels\IMetaModel;
use MetaModels\ItemList;
use MetaModels\FrontendIntegration\HybridList;
use MetaModels\ContaoFrontendEditingBundle\FrontendEditHybrid;

/**
 * This tests the RenderItemListListener.
 */
class RenderItemListListenerTest extends TestCase
{
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
        $factory         = $this->getMockForAbstractClass(IFactory::class);
        $eventDispatcher = new EventDispatcher();
        $frontendEditor  = new FrontendEditor($eventDispatcher, $translator);

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
    public function testHandleForItemRenderingAddsWithEditFlag()
    {
        $GLOBALS['TL_LANG']['MSC']['metamodel_edit_item'] = 'Edit label';

        $metaModel      = $this->getMockForAbstractClass(IMetaModel::class);
        $renderSettings = $this->getMockForAbstractClass(ICollection::class);
        $item           = $this->getMockForAbstractClass(IItem::class);
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $dispatcher     = new EventDispatcher();
        $frontendEditor = new FrontendEditor($dispatcher, $translator);

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
        $itemList       = $this->createMock(ItemList::class);
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $dispatcher     = new EventDispatcher();
        $frontendEditor = new FrontendEditor($dispatcher, $translator);
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
        $renderSettings = $this->getMockForAbstractClass(ICollection::class);
        $dispatcher     = new EventDispatcher();
        $itemList       = $this->createMock(ItemList::class);
        $template       = new Template();
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor = new FrontendEditor($dispatcher, $translator);
        $caller         = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $caller->Template = new \stdClass();

        $itemList
            ->expects($this->any())
            ->method('getView')
            ->willReturn($renderSettings);

        /** @var ICollection $renderSettings */

        $event    = new RenderItemListEvent($itemList, $template, $caller);
        $listener = new RenderItemListListener($translator, $dispatcher, $factory, $frontendEditor);

        $listener->handleFrontendEditingInListRendering($event, MetaModelsEvents::RENDER_ITEM_LIST, $dispatcher);

        $this->assertEquals(false, $template->editEnable);
        $this->assertEquals(false, $caller->Template->editEnable);
    }

    /**
     * Test that the compile method works correctly.
     *
     * @return void
     */
    public function testFrontendEditingInListRenderingRevertsWithoutPage()
    {
        $dispatcher     = new EventDispatcher();
        $metaModel      = $this->getMockForAbstractClass(IMetaModel::class);
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $renderSettings = new Collection($metaModel, [], $dispatcher, null);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor = new FrontendEditor($dispatcher, $translator);
        $itemList       = $this->createMock(ItemList::class);
        $template       = new Template();
        $caller         = $this
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
        $metaModel      = $this->getMockForAbstractClass(IMetaModel::class);
        $dispatcher     = new EventDispatcher();
        $renderSettings = new Collection($metaModel, [], $dispatcher, null);
        $itemList       = $this->createMock(ItemList::class);
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor = new FrontendEditor($dispatcher, $translator);
        $template       = new Template();
        $caller         = $this
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
    public function testFrontendEditingInListRenderingAddsWithEditFlag()
    {
        $GLOBALS['TL_LANG']['MSC']['metamodel_edit_item'] = 'Edit label';
        $GLOBALS['TL_LANG']['MSC']['metamodel_add_item']  = 'Add label';

        $metaModel      = $this->getMockForAbstractClass(IMetaModel::class);
        $dispatcher     = new EventDispatcher();
        $renderSettings = new Collection($metaModel, [], $dispatcher, null);
        $itemList       = $this->createMock(ItemList::class);
        $translator     = $this->getMockForAbstractClass(TranslatorInterface::class);
        $factory        = $this->getMockForAbstractClass(IFactory::class);
        $frontendEditor = new FrontendEditor($dispatcher, $translator);
        $template       = new Template();
        $caller         = $this
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

        $listener->handleFrontendEditingInListRendering($event, MetaModelsEvents::RENDER_ITEM_LIST, $dispatcher);

        $this->assertEquals(true, $template->editEnable);
    }
}
