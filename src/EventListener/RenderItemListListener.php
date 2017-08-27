<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2016-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage ContaoFrontendEditing
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2016-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Contao\FrontendEditing\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\FrontendIntegration\HybridList;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class handles the processing of list rendering.
 */
class RenderItemListListener
{
    /**
     * This property will get set on the render setting collection.
     */
    const FRONTEND_EDITING_ENABLED_FLAG = '$frontend-editing-enabled';

    /**
     * This property holds the frontend editing page array.
     */
    const FRONTEND_EDITING_PAGE = '$frontend-editing-page';

    /**
     * Handle the url injection for item rendering.
     *
     * @param ParseItemEvent $event The event to process.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleForItemRendering(ParseItemEvent $event)
    {
        $settings = $event->getRenderSettings();
        if (!$settings->get(self::FRONTEND_EDITING_ENABLED_FLAG)) {
            return;
        }

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = func_get_arg(2);
        $parsed     = $event->getResult();
        $item       = $event->getItem();

        $parsed['actions']['edit'] = [
            'label' => $GLOBALS['TL_LANG']['MSC']['metamodel_edit_item'],
            'href'  => $this->generateEditUrl(
                $dispatcher,
                $settings->get(self::FRONTEND_EDITING_PAGE),
                ModelId::fromValues($item->getMetaModel()->getTableName(), $event->getItem()->get('id'))
                    ->getSerialized()
            ),
            'class' => 'edit',
        ];

        $event->setResult($parsed);
    }

    /**
     * Process the event.
     *
     * @param RenderItemListEvent $event The event to process.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleFrontendEditingInListRendering(RenderItemListEvent $event)
    {
        $caller = $event->getCaller();
        if (!($caller instanceof HybridList)) {
            return;
        }

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = func_get_arg(2);
        $enabled    = (bool) $caller->metamodel_fe_editing;
        if ($enabled) {
            $page    = $this->getPageDetails($dispatcher, $caller->metamodel_fe_editing_page);
            $enabled = (null !== $page);
        }

        $event->getTemplate()->editEnable = $caller->Template->editEnable = $enabled;
        $event->getList()->getView()->set(self::FRONTEND_EDITING_ENABLED_FLAG, $enabled);
        if ($enabled) {
            $url = $this->generateAddUrl($dispatcher, $page);

            $caller->Template->addUrl      = $url;
            $caller->Template->addNewLabel = $GLOBALS['TL_LANG']['MSC']['metamodel_add_item'];
            $event->getTemplate()->addUrl  = $url;

            $event->getList()->getView()->set(self::FRONTEND_EDITING_PAGE, $page);
        }
    }

    /**
     * Generate the url to add an item.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @param array                    $page       The page details.
     *
     * @return string
     */
    private function generateAddUrl(EventDispatcherInterface $dispatcher, array $page)
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        $url = UrlBuilder::fromUrl($event->getUrl() . '?')
            ->setQueryParameter('act', 'create');

        return $url->getUrl();
    }

    /**
     * Generate the url to edit an item.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @param array                    $page       The page details.
     *
     * @param string                   $itemId     The id of the item.
     *
     * @return string
     */
    private function generateEditUrl(EventDispatcherInterface $dispatcher, array $page, $itemId)
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        $url = UrlBuilder::fromUrl($event->getUrl() . '?')
            ->setQueryParameter('act', 'edit')
            ->setQueryParameter('id', $itemId);

        return $url->getUrl();
    }

    /**
     * Retrieve the details for the page with the given id.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param string                   $pageId     The id of the page to retrieve the details for.
     *
     * @return array
     */
    private function getPageDetails(EventDispatcherInterface $dispatcher, $pageId)
    {
        if (empty($pageId)) {
            return null;
        }
        $event = new GetPageDetailsEvent($pageId);
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);

        return $event->getPageDetails();
    }
}
