<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Contao\FrontendEditing\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use MetaModels\Contao\FrontendEditing\EventListener\RenderItemListListener;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\IItem;
use MetaModels\MetaModelsEvents;
use MetaModels\Render\Setting\Collection;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Template;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This tests the RenderItemListListener.
 */
class RenderItemListListenerTest extends TestCase
{
    /**
     * This is the hack to mimic the Contao auto loader.
     *
     * @return void
     */
    public static function contaoAutoload($class)
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
    protected function setUp()
    {
        parent::setUp();
        // This is the hack to mimic the Contao auto loader.
        spl_autoload_register(self::class . '::contaoAutoload');
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        spl_autoload_unregister(self::class . '::contaoAutoload');
        parent::tearDown();
    }

    /**
     * Test that the method works correctly.
     *
     * @return void
     */
    public function testHandleForItemRenderingDoesNothingWithoutEditFlag()
    {
        $renderSettings = $this->getMockForAbstractClass('MetaModels\Render\Setting\ICollection');
        $item           = $this->getMockForAbstractClass('MetaModels\IItem');

        /** @var ICollection $renderSettings */
        /** @var IItem $item */

        $event    = new ParseItemEvent($renderSettings, $item, 'html5', []);
        $listener = new RenderItemListListener();

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

        $metaModel      = $this->getMockForAbstractClass('MetaModels\IMetaModel');
        $renderSettings = $this->getMockForAbstractClass('MetaModels\Render\Setting\ICollection');
        $item           = $this->getMockForAbstractClass('MetaModels\IItem');
        $dispatcher     = new EventDispatcher();

        $metaModel->expects($this->any())->method('getTableName')->willReturn('mm_test');
        $item
            ->expects($this->any())
            ->method('getMetaModel')
            ->willReturn($metaModel);
        $item
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($name) {
                switch ($name) {
                    case 'id':
                        return 'item-id';
                    default:
                }
                return null;
            });

        $renderSettings
            ->expects($this->any())
            ->method('get')
            ->with()
            ->willReturnCallback(function ($name) {
                switch ($name) {
                    case RenderItemListListener::FRONTEND_EDITING_ENABLED_FLAG:
                        return true;
                    case RenderItemListListener::FRONTEND_EDITING_PAGE:
                        return ['id' => 11, 'language' => 'en', 'alias' => 'test-page'];
                    default:
                }
                return null;
            });

        /** @var ICollection $renderSettings */
        /** @var IItem $item */

        $event    = new ParseItemEvent($renderSettings, $item, 'html5', []);
        $listener = new RenderItemListListener();

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
    public function testFrontendEditingInListRenderingDoesNothingForInvalidCaller()
    {
        $itemList = $this->getMockBuilder('MetaModels\ItemList')->getMock();
        $template = new Template();
        $event    = new RenderItemListEvent($itemList, $template, new \DateTime());
        $listener = new RenderItemListListener();

        $listener->handleFrontendEditingInListRendering($event);

        $this->assertEquals(null, $template->editEnable);
    }

    /**
     * Test that the method works correctly.
     *
     * @return void
     */
    public function testFrontendEditingInListRenderingSetFlagsWithEditFlagBeingFalse()
    {
        $renderSettings = $this->getMockForAbstractClass('MetaModels\Render\Setting\ICollection');
        $dispatcher     = new EventDispatcher();
        $itemList       = $this->getMockBuilder('MetaModels\ItemList')->setMethods(['getView'])->getMock();
        $template       = new Template();
        $caller         = $this
            ->getMockBuilder('MetaModels\FrontendIntegration\HybridList')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $caller->Template = new \stdClass();

        $itemList
            ->expects($this->any())
            ->method('getView')
            ->willReturn($renderSettings);

        /** @var ICollection $renderSettings */

        $event    = new RenderItemListEvent($itemList, $template, $caller);
        $listener = new RenderItemListListener();

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
        $metaModel      = $this->getMockForAbstractClass('MetaModels\IMetaModel');
        $renderSettings = new Collection($metaModel, []);
        $dispatcher     = new EventDispatcher();
        $itemList       = $this->getMockBuilder('MetaModels\ItemList')->setMethods(['getView'])->getMock();
        $template       = new Template();
        $caller         = $this
            ->getMockBuilder('MetaModels\Contao\FrontendEditing\FrontendEditHybrid')
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
        $listener = new RenderItemListListener();

        $listener->handleFrontendEditingInListRendering($event, MetaModelsEvents::RENDER_ITEM_LIST, $dispatcher);

        $this->assertEquals(false, $template->editEnable);
    }

    /**
     * Test that the compile method works correctly.
     *
     * @return void
     */
    public function testFrontendEditingInListRenderingRevertsWithoutPageDetails()
    {
        $metaModel      = $this->getMockForAbstractClass('MetaModels\IMetaModel');
        $renderSettings = new Collection($metaModel, []);
        $dispatcher     = new EventDispatcher();
        $itemList       = $this->getMockBuilder('MetaModels\ItemList')->setMethods(['getView'])->getMock();
        $template       = new Template();
        $caller         = $this
            ->getMockBuilder('MetaModels\FrontendIntegration\HybridList')
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
        $listener = new RenderItemListListener();

        $listener->handleFrontendEditingInListRendering($event, MetaModelsEvents::RENDER_ITEM_LIST, $dispatcher);

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

        $metaModel      = $this->getMockForAbstractClass('MetaModels\IMetaModel');
        $renderSettings = new Collection($metaModel, []);
        $dispatcher     = new EventDispatcher();
        $itemList       = $this->getMockBuilder('MetaModels\ItemList')->setMethods(['getView'])->getMock();
        $template       = new Template();
        $caller         = $this
            ->getMockBuilder('MetaModels\FrontendIntegration\HybridList')
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
        $listener = new RenderItemListListener();

        $listener->handleFrontendEditingInListRendering($event, MetaModelsEvents::RENDER_ITEM_LIST, $dispatcher);

        $this->assertEquals(true, $template->editEnable);
    }
}
