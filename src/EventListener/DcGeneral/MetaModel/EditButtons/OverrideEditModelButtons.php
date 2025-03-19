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

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\EditButtons;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\TraitFrontendScope;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This overrides the buttons.
 */
class OverrideEditModelButtons
{
    use TraitFrontendScope;

    /**
     * The view combination.
     *
     * @var ViewCombination
     */
    private ViewCombination $viewCombination;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * The constructor.
     *
     * @param ViewCombination     $viewCombination The view combination.
     * @param TranslatorInterface $translator      The translator.
     */
    public function __construct(ViewCombination $viewCombination, TranslatorInterface $translator)
    {
        $this->viewCombination = $viewCombination;
        $this->translator      = $translator;
    }

    /**
     * Invoke the event.
     *
     * @param GetEditModeButtonsEvent $event The event.
     *
     * @return void
     */
    public function __invoke(GetEditModeButtonsEvent $event): void
    {
        if (!$this->wantToHandle($event) || (null === ($buttons = $this->findOverrides($event)))) {
            return;
        }

        $this->override($event, $buttons);
    }

    /**
     * Override the buttons.
     *
     * @param GetEditModeButtonsEvent $event   The event.
     * @param array                   $buttons The buttons for override.
     *
     * @return void
     */
    private function override(GetEditModeButtonsEvent $event, array $buttons): void
    {
        $addButtons = [];

        $buttonTemplate = '<button type="submit" name="%s" id="%s" class="submit %s%s"%s>%s</button>';

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        foreach ($buttons as $button) {
            if (empty($button['name'])) {
                continue;
            }

            // If label not empty, try to translate at contao default -
            // if label empty, translate at model name as domain
            if ('' === $label = $button['label']) {
                $label = $this->translator->trans($button['name'], [], $dataDefinition->getName());
            } else {
                $label = $this->translator->trans($label, [], 'contao_default');
            }


            $addButton = \sprintf(
                $buttonTemplate,
                $button['name'],
                $button['name'],
                $button['name'],
                $button['notSave'] ? ' notsave' : '',
                ($button['attributes'] ? ' ' . \html_entity_decode($button['attributes']) : ''),
                $label
            );

            $addButtons[$button['name']] = $addButton;
        }

        $event->setButtons($addButtons);
    }

    /**
     * Find the override buttons.
     *
     * @param GetEditModeButtonsEvent $event The event.
     *
     * @return array|null
     */
    private function findOverrides(GetEditModeButtonsEvent $event): ?array
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        $inputScreen = $this->viewCombination->getScreen($dataDefinition->getName());
        if (
            null === $inputScreen
            || !isset($inputScreen['meta']['fe_overrideEditButtons'], $inputScreen['meta']['fe_editButtons'])
            || !$inputScreen['meta']['fe_overrideEditButtons']
        ) {
            return null;
        }

        return $inputScreen['meta']['fe_editButtons']
            ? \unserialize($inputScreen['meta']['fe_editButtons'], ['allowed_classes' => false]) : [];
    }
}
