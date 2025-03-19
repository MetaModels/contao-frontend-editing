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
use Contao\CoreBundle\String\SimpleTokenParser;
use Contao\PageModel;
use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
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
     * The token parser.
     *
     * @var SimpleTokenParser
     */
    private SimpleTokenParser $tokenParser;

    /**
     * The insert-tags parser.
     *
     * @var InsertTagParser
     */
    private InsertTagParser $insertTagParser;

    /**
     * The constructor.
     *
     * @param ViewCombination   $viewCombination   The view combination.
     * @param Adapter           $pageService       The page model service.
     * @param Adapter           $stringUtilService The string util service.
     * @param SimpleTokenParser $tokenParser       The token parser.
     * @param InsertTagParser   $insertTagParser   The insert-tags parser.
     */
    public function __construct(
        ViewCombination $viewCombination,
        Adapter $pageService,
        Adapter $stringUtilService,
        SimpleTokenParser $tokenParser,
        InsertTagParser $insertTagParser
    ) {
        $this->viewCombination   = $viewCombination;
        $this->pageService       = $pageService;
        $this->stringUtilService = $stringUtilService;
        $this->tokenParser       = $tokenParser;
        $this->insertTagParser   = $insertTagParser;
    }

    /**
     * Invoke the event.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     */
    public function __invoke(ActionEvent $event): void
    {
        if (
            !\in_array($event->getAction()->getName(), ['create', 'edit'])
            || !$this->wantToHandle($event)
            || (null === ($button = $this->findButton($event)))
        ) {
            return;
        }

        $inputProvider = $event->getEnvironment()->getInputProvider();
        $dataProvider  = $event->getEnvironment()->getDataProvider();
        assert($inputProvider instanceof InputProviderInterface);
        assert($dataProvider instanceof DataProviderInterface);

        $idValue = $inputProvider->getParameter('id');
        if ($idValue) {
            $model = $dataProvider->fetch(
                $dataProvider->getEmptyConfig()->setId(ModelId::fromSerialized($idValue)->getId())
            );

            if (null !== $model) {
                $tokenData = [];
                // Get model properties.
                foreach ($model->getPropertiesAsArray() as $keyData => $valueData) {
                    $tokenData['model_' . $keyData] = $valueData;
                }

                // Replace simple tokens.
                $button = $this->replaceSimpleTokensAtJumpToParameter($button, $tokenData);
            }
        }

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
     * @throws DcGeneralRuntimeException The forward setting is missing, for button name.
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
     * @param ActionEvent $event The event.
     *
     * @return array|null
     */
    private function findButton(ActionEvent $event): ?array
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

        $inputProvider = $event->getEnvironment()->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $findButton = null;
        foreach ($buttons as $button) {
            if (!$button['notSave'] || !$inputProvider->hasValue($button['name'])) {
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
                $this->tokenParser->parse(
                    \str_replace('&#35;', '#', $button['jumpToParameter']),
                    $tokenData
                );
        }

        return $button;
    }
}
