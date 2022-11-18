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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\EditButtons\Action;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\PageModel;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\TraitFrontendScope;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This is for a model edit button is declared for not save.
 */
class NotSaveEditModelButton
{
    use TraitFrontendScope;

    /**
     * The view combination.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * The page model service.
     *
     * @var Adapter|PageModel
     */
    private $pageService;

    /**
     * The constructor.
     *
     * @param ViewCombination $viewCombination The view combination.
     * @param Adapter         $pageService     The page model service.
     */
    public function __construct(ViewCombination $viewCombination, Adapter $pageService)
    {
        $this->viewCombination = $viewCombination;
        $this->pageService     = $pageService;
    }

    /**
     * Ivoke the event.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     */
    public function __invoke(ActionEvent $event): void
    {
        if (!\in_array($event->getAction()->getName(), ['create', 'edit'])
            || !$this->wantToHandle($event)
            || (null === ($button = $this->findButton($event)))) {
            return;
        }

        $this->forwardTo($button);
    }

    /**
     * Forward to the declared page.
     *
     * @param array $button The button.
     *
     * @return void
     *
     * @throws RedirectResponseException When jump to is empty.
     */
    private function forwardTo(array $button): void
    {
        if (empty($button['jumpTo'])) {
            throw new DcGeneralRuntimeException('The forward setting is missing, for button name ' . $button['name']);
        }

        // @codingStandardsIgnoreStart
        // FIXME: Use page tree if this work with mcw.
        // @codingStandardsIgnoreEnd
        $pageId = \explode('::', \trim($button['jumpTo'], '{{}}'))[1];
        /** @var PageModel $pageModel */
        $pageModel       = $this->pageService->findByIdOrAlias($pageId);
        $jumpToParameter = \html_entity_decode(($button['jumpToParameter'] ?? ''));
        if (0 === \strpos($jumpToParameter, '?')) {
            $url = $pageModel->getAbsoluteUrl() . $jumpToParameter;
        } else {
            $url = $pageModel->getAbsoluteUrl($jumpToParameter ? '/' . \ltrim($jumpToParameter, '/') : '');
        }

        throw new RedirectResponseException($url);
    }

    /**
     * Find the used edit model button.
     *
     * @param ActionEvent $event The event.
     *
     * @return array|null
     */
    private function findButton(ActionEvent $event): ?array
    {
        $inputScreen = $this->viewCombination->getScreen($event->getEnvironment()->getDataDefinition()->getName());
        if (!$inputScreen
            || !isset($inputScreen['meta']['fe_overrideEditButtons'], $inputScreen['meta']['fe_editButtons'])
            || !$inputScreen['meta']['fe_overrideEditButtons']
            || !($buttons = $inputScreen['meta']['fe_editButtons'])
        ) {
            return null;
        }

        if (\is_string($buttons)) {
            $buttons = \unserialize($buttons, ['allowed_classes' => false]);
        }

        $findButton    = null;
        $inputProvider = $event->getEnvironment()->getInputProvider();
        foreach ($buttons as $button) {
            if (!$button['notSave'] || !$inputProvider->hasValue($button['name'])) {
                continue;
            }

            $findButton = $button;
            break;
        }

        return $findButton;
    }
}
