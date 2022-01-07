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
use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\Event\HandleSubmitEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\TraitFrontendScope;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This is for a model edit button is declared for save and forward.
 */
class ForwardSaveEditModelButton
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
     * The string util service.
     *
     * @var Adapter|StringUtil
     */
    private $stringUtilService;

    /**
     * The constructor.
     *
     * @param ViewCombination $viewCombination   The view combination.
     * @param Adapter         $pageService       The page model service.
     * @param Adapter         $stringUtilService The string util service.
     */
    public function __construct(ViewCombination $viewCombination, Adapter $pageService, Adapter $stringUtilService)
    {
        $this->viewCombination   = $viewCombination;
        $this->pageService       = $pageService;
        $this->stringUtilService = $stringUtilService;
    }

    /**
     * Invoke the event.
     *
     * @param HandleSubmitEvent $event The event.
     */
    public function __invoke(HandleSubmitEvent $event): void
    {
        if (!$this->wantToHandle($event)
            || (null === ($button = $this->findButton($event)))
        ) {
            return;
        }

        $tokenData = [];
        // Get model properties.
        foreach ($event->getModel()->getPropertiesAsArray() as $keyData => $valueData){
            $tokenData['model_' . $keyData] = $valueData;
        }

        // Replace simple tokens.
        $button = $this->replaceSimpleTokensAtJumpToParameter($button, $tokenData);

        $this->forwardTo($button);
    }

    /**
     * Forward to the declared page.
     *
     * @param array $button    The button.
     *
     * @return void
     */
    private function forwardTo(array $button): void
    {
        // FIXME: Use page tree if this work with mcw.
        $pageId = \explode('::', \trim($button['jumpTo'], '{{}}'))[1];
        /** @var PageModel $pageModel */
        $pageModel       = $this->pageService->findByIdOrAlias($pageId);
        $jumpToParameter = \html_entity_decode($button['jumpToParameter'] ?? '');

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
     * @param HandleSubmitEvent $event The event.
     *
     * @return array|null
     */
    private function findButton(HandleSubmitEvent $event): ?array
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
            if (!$button['jumpTo'] || !$inputProvider->hasValue($button['name'])) {
                continue;
            }

            $findButton = $button;
            break;
        }

        return $findButton;
    }

    /**
     * Replace simple tokens at button parameter 'jumpToParameter'.
     *
     * @param array $button    The button.
     * @param array $tokenData The token data.
     *
     * @return array
     */
    private function replaceSimpleTokensAtJumpToParameter(array $button, array $tokenData): array
    {
        if (false !== strpos($button['jumpToParameter'], '&#35;&#35;')
            || false !== strpos($button['jumpToParameter'], '##')) {
            $button['jumpToParameter'] =
                $this->stringUtilService->parseSimpleTokens(str_replace('&#35;', '#', $button['jumpToParameter']), $tokenData);
        }

        return $button;
    }
}
