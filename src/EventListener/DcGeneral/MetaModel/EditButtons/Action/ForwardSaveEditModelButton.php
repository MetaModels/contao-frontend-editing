<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\EditButtons\Action;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\Event\HandleSubmitEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
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
    private ViewCombination $viewCombination;

    /**
     * The page model service.
     *
     * @var Adapter|PageModel
     */
    private Adapter|PageModel $pageService;

    /**
     * The string util service.
     *
     * @var Adapter|StringUtil
     */
    private Adapter|StringUtil $stringUtilService;

    /**
     * The insert-tags parser.
     *
     * @var InsertTagParser
     */
    private InsertTagParser $insertTagParser;

    /**
     * The constructor.
     *
     * @param ViewCombination $viewCombination   The view combination.
     * @param Adapter         $pageService       The page model service.
     * @param Adapter         $stringUtilService The string util service.
     * @param InsertTagParser $insertTagParser   The insert-tags parser.
     */
    public function __construct(
        ViewCombination $viewCombination,
        Adapter $pageService,
        Adapter $stringUtilService,
        InsertTagParser $insertTagParser
    ) {
        $this->viewCombination   = $viewCombination;
        $this->pageService       = $pageService;
        $this->stringUtilService = $stringUtilService;
        $this->insertTagParser   = $insertTagParser;
    }

    /**
     * Invoke the event.
     *
     * @param HandleSubmitEvent $event The event.
     *
     * @return void
     */
    public function __invoke(HandleSubmitEvent $event): void
    {
        if (
            !$this->wantToHandle($event)
            || (null === ($button = $this->findButton($event)))
        ) {
            return;
        }

        $tokenData = [];
        // Get model properties.
        foreach ($event->getModel()->getPropertiesAsArray() as $keyData => $valueData) {
            $tokenData['model_' . $keyData] = $valueData;
        }

        // Replace simple tokens.
        $button = $this->replaceSimpleTokensAtJumpToParameter($button, $tokenData);

        $button['jumpToParameter'] = $this->insertTagParser->replace($button['jumpToParameter']);

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
        // @codingStandardsIgnoreStart
        // FIXME: Use page tree if this work with mcw.
        // @codingStandardsIgnoreEnd
        $pageId = \explode('::', \trim($button['jumpTo'], '{{}}'))[1];
        /**
         * @var PageModel $pageModel
         * @psalm-suppress InternalMethod - Class ContaoFramework is internal, not the getAdapter() method.
         */
        $pageModel       = $this->pageService->findByIdOrAlias($pageId);
        $jumpToParameter = \html_entity_decode(($button['jumpToParameter'] ?? ''));

        if (\str_starts_with($jumpToParameter, '?')) {
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
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        $inputScreen = $this->viewCombination->getScreen($dataDefinition->getName());
        if (
            null === $inputScreen
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
        assert($inputProvider instanceof InputProviderInterface);

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
        if (
            \str_contains($button['jumpToParameter'], '&#35;&#35;')
            || \str_contains($button['jumpToParameter'], '##')
        ) {
            $button['jumpToParameter'] =
                System::getContainer()->get('contao.string.simple_token_parser')?->parse(
                    \str_replace('&#35;', '#', $button['jumpToParameter']),
                    $tokenData
                );
        }

        return $button;
    }
}
