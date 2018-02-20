<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2016-2018 The MetaModels team.
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
 * @copyright  2016-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\FrontendIntegration\HybridList;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * RenderItemListListener constructor.
     *
     * @param TranslatorInterface      $translator The translator.
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(TranslatorInterface $translator, EventDispatcherInterface $dispatcher)
    {
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the url injection for item rendering.
     *
     * @param ParseItemEvent $event The event to process.
     *
     * @return void
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function handleForItemRendering(ParseItemEvent $event)
    {
        $settings = $event->getRenderSettings();
        if (!$settings->get(self::FRONTEND_EDITING_ENABLED_FLAG)) {
            return;
        }

        $parsed = $event->getResult();
        $item   = $event->getItem();

        $parsed['actions']['edit'] = [
            'label' => $this->translator->trans('MSC.metamodel_edit_item', [], 'contao_default'),
            'href'  => $this->generateEditUrl(
                $settings->get(self::FRONTEND_EDITING_PAGE),
                ModelId::fromValues($item->getMetaModel()->getTableName(), $event->getItem()->get('id'))
                    ->getSerialized()
            ),
            'class' => 'edit',
        ];
        $parsed['actions']['copy'] = [
            'label' => $this->translator->trans('MSC.metamodel_copy_item', [], 'contao_default'),
            'href'  => $this->generateCopyUrl(
                $settings->get(self::FRONTEND_EDITING_PAGE),
                ModelId::fromValues($item->getMetaModel()->getTableName(), $event->getItem()->get('id'))
                    ->getSerialized()
            ),
            'class' => 'copy',
        ];
        $parsed['actions']['delete'] = [
            'label'     => $this->translator->trans('MSC.metamodel_delete_item', [], 'contao_default'),
            'href'      => $this->generateDeleteUrl(
                $settings->get(self::FRONTEND_EDITING_PAGE),
                ModelId::fromValues($item->getMetaModel()->getTableName(), $event->getItem()->get('id'))
                    ->getSerialized()
            ),
            'attribute' => sprintf(
                'onclick="if (!confirm(\'%s\')) return false;"',
                $this->translator->trans('MSC.deleteConfirm', [$event->getItem()->get('id')], 'contao_default')
            ),
            'class'     => 'delete',
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
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function handleFrontendEditingInListRendering(RenderItemListEvent $event)
    {
        $caller = $event->getCaller();
        if (!($caller instanceof HybridList)) {
            return;
        }

        $enabled = (bool)$caller->metamodel_fe_editing;
        if ($enabled) {
            $page    = $this->getPageDetails($caller->metamodel_fe_editing_page);
            $enabled = (null !== $page);
        }

        $event->getTemplate()->editEnable = $caller->Template->editEnable = $enabled;
        $event->getList()->getView()->set(self::FRONTEND_EDITING_ENABLED_FLAG, $enabled);
        if ($enabled) {
            $url = $this->generateAddUrl($page);

            $caller->Template->addUrl      = $url;
            $caller->Template->addNewLabel = $this->translator->trans('MSC.metamodel_add_item', [], 'contao_default');
            $event->getTemplate()->addUrl  = $url;

            $event->getList()->getView()->set(self::FRONTEND_EDITING_PAGE, $page);
        }
    }

    /**
     * Generate the url to add an item.
     *
     * @param array $page The page details.
     *
     * @return string
     */
    private function generateAddUrl(array $page)
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        $url = UrlBuilder::fromUrl($event->getUrl().'?')
            ->setQueryParameter('act', 'create');

        return $url->getUrl();
    }

    /**
     * Generate the url to edit an item.
     *
     * @param array  $page   The page details.
     *
     * @param string $itemId The id of the item.
     *
     * @return string
     */
    private function generateEditUrl(array $page, $itemId)
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        $url = UrlBuilder::fromUrl($event->getUrl().'?')
            ->setQueryParameter('act', 'edit')
            ->setQueryParameter('id', $itemId);

        return $url->getUrl();
    }

    /**
     * Generate the url to edit an item.
     *
     * @param array  $page   The page details.
     *
     * @param string $itemId The id of the item.
     *
     * @return string
     */
    private function generateCopyUrl(array $page, $itemId)
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        $url = UrlBuilder::fromUrl($event->getUrl().'?')
            ->setQueryParameter('act', 'copy')
            ->setQueryParameter('source', $itemId);

        return $url->getUrl();
    }

    /**
     * Generate the url to delete an item.
     *
     * @param array  $page   The page details.
     *
     * @param string $itemId The id of the item.
     *
     * @return string
     */
    private function generateDeleteUrl(array $page, $itemId)
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        $url = UrlBuilder::fromUrl($event->getUrl().'?')
            ->setQueryParameter('act', 'delete')
            ->setQueryParameter('id', $itemId);

        return $url->getUrl();
    }

    /**
     * Retrieve the details for the page with the given id.
     *
     * @param string $pageId The id of the page to retrieve the details for.
     *
     * @return array
     */
    private function getPageDetails($pageId)
    {
        if (empty($pageId)) {
            return null;
        }
        $event = new GetPageDetailsEvent($pageId);
        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);

        return $event->getPageDetails();
    }
}
