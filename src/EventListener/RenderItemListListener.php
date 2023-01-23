<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\FrontendEditor;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Contao\FrontendUser;
use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\IFactory;
use MetaModels\ViewCombination\InputScreenInformationBuilder;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The frontend editor.
     *
     * @var FrontendEditor
     */
    private $frontendEditor;

    /**
     * The security.
     *
     * @var Security
     */
    private Security $security;

    /**
     * The input screen information builder.
     *
     * @var InputScreenInformationBuilder
     */
    private InputScreenInformationBuilder $inputScreens;

    /**
     * RenderItemListListener constructor.
     *
     * @param TranslatorInterface           $translator     The translator.
     * @param EventDispatcherInterface      $dispatcher     The event dispatcher.
     * @param IFactory                      $factory        The MetaModels factory.
     * @param FrontendEditor                $frontendEditor The DCGeneral frontend editor.
     * @param Security                      $security       The security.
     * @param InputScreenInformationBuilder $inputScreens   The input screen information builder.
     */
    public function __construct(
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        IFactory $factory,
        FrontendEditor $frontendEditor,
        Security $security,
        InputScreenInformationBuilder $inputScreens
    ) {
        $this->translator     = $translator;
        $this->dispatcher     = $dispatcher;
        $this->factory        = $factory;
        $this->frontendEditor = $frontendEditor;
        $this->security       = $security;
        $this->inputScreens   = $inputScreens;
    }

    /**
     * Handle the url injection for item rendering.
     *
     * @param ParseItemEvent $event The event to process.
     *
     * @return void
     */
    public function handleForItemRendering(ParseItemEvent $event): void
    {
        $settings = $event->getRenderSettings();
        if (!$settings->get(self::FRONTEND_EDITING_ENABLED_FLAG)) {
            return;
        }

        $parsed          = $event->getResult();
        $item            = $event->getItem();
        $tableName       = $item->getMetaModel()->getTableName();
        $definition      = $this->frontendEditor->createDcGeneral($tableName)->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $editingPage     = $settings->get(self::FRONTEND_EDITING_PAGE);
        $modelId         = ModelId::fromValues($tableName, $item->get('id'))->getSerialized();

        // Check FEE permissions of member for item.
        $isEditableForMember = true;
        /** @var IMetaModelDefinition $metaModels */
        $metaModel  = $definition->getDefinition(IMetaModelDefinition::NAME);
        $screen     = $this->inputScreens->fetchInputScreens([$tableName => $metaModel->getActiveInputScreen()]);
        $screenMeta = $screen[$tableName]['meta'];

        if (!empty($screenMeta['fe_useMemberPermissions'])
            && !empty($memberAttribut = $screenMeta['fe_memberAttribut'])) {
            // Reset permissions.
            $isEditableForMember = false;

            // Get username of member.
            $user      = $this->security->getUser();
            $itemValue = $item->parseAttribute($memberAttribut,'text');
            $username  = $itemValue['raw']['username'] ?? '';

            // Add edit links, for member with enough permissions or
            // member attribute is not in render settings.
            if ((null === $itemValue['raw'])
                || (($user instanceof FrontendUser)
                    && $username === $user->getUserIdentifier())) {
                $isEditableForMember = true;
            }
        }

        // Add edit action
        if ($basicDefinition->isEditable() && $isEditableForMember) {
            $parsed['actions']['edit'] = [
                'label' => $this->translateLabel('metamodel_edit_item', $definition->getName()),
                'href'  => $this->generateEditUrl($editingPage, $modelId),
                'class' => 'edit',
            ];
        }

        // Add copy action
        if ($basicDefinition->isCreatable() && $isEditableForMember) {
            $parsed['actions']['copy'] = [
                'label' => $this->translateLabel('metamodel_copy_item', $definition->getName()),
                'href'  => $this->generateCopyUrl($editingPage, $modelId),
                'class' => 'copy',
            ];
        }

        // Add create variant action
        if (false === $item->isVariant()
            && $basicDefinition->isCreatable()
            && $item->getMetaModel()->hasVariants()
            && $isEditableForMember) {
            $parsed['actions']['createvariant'] = [
                'label' => $this->translateLabel('metamodel_create_variant', $definition->getName()),
                'href'  => $this->generateCreateVariantUrl($editingPage, $modelId),
                'class' => 'createvariant',
            ];
        }

        // Add delete action
        if ($basicDefinition->isDeletable() && $isEditableForMember) {
            $parsed['actions']['delete'] = [
                'label'     => $this->translateLabel('metamodel_delete_item', $definition->getName()),
                'href'      => $this->generateDeleteUrl($editingPage, $modelId),
                'attribute' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false;"',
                    $this->translator->trans('MSC.deleteConfirm', [$item->get('id')], 'contao_default')
                ),
                'class'     => 'delete',
            ];
        }

        $event->setResult($parsed);
    }

    /**
     * Process the event.
     *
     * @param RenderItemListEvent $event The event to process.
     *
     * @return void
     */
    public function handleFrontendEditingInListRendering(RenderItemListEvent $event): void
    {
        $model = $event->getList()->getModel();
        if (null === $model) {
            return;
        }

        $page    = null;
        $enabled = (bool) $model->metamodel_fe_editing;
        if ($enabled) {
            $page    = $this->getPageDetails($model->metamodel_fe_editing_page);
            $enabled = (null !== $page);

            $view = $event->getList()->getView();

            $view->set(self::FRONTEND_EDITING_PAGE, $page);
            $view->set(self::FRONTEND_EDITING_ENABLED_FLAG, $enabled);
        }

        $listTemplate = $event->getList()->getListTemplate();
        if ($enabled) {
            $tableName  = $this->factory->translateIdToMetaModelName($model->metamodel);
            $definition = $this->frontendEditor->createDcGeneral($tableName)->getDataDefinition();
            $enabled    = $definition->getBasicDefinition()->isCreatable();

            if ($enabled) {
                $url = $this->generateAddUrl($page);
                if (null !== $listTemplate) {
                    $listTemplate->addUrl      = $url;
                    $listTemplate->addNewLabel = $this->translateLabel('metamodel_add_item', $tableName);
                }
                $event->getTemplate()->addUrl = $url;
            }
        }

        $event->getTemplate()->editEnable = $enabled;
        if (null !== $listTemplate) {
            $listTemplate->editEnable = $enabled;
        }
    }

    /**
     * Generate the url to add an item.
     *
     * @param array $page The page details.
     *
     * @return string
     */
    private function generateAddUrl(array $page): string
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL);

        $url = UrlBuilder::fromUrl($event->getUrl() . '?')
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
    private function generateEditUrl(array $page, $itemId): string
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL);

        $url = UrlBuilder::fromUrl($event->getUrl() . '?')
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
    private function generateCopyUrl(array $page, $itemId): string
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL);

        $url = UrlBuilder::fromUrl($event->getUrl() . '?')
            ->setQueryParameter('act', 'copy')
            ->setQueryParameter('source', $itemId);

        return $url->getUrl();
    }

    /**
     * Generate the url to create a variant for an item.
     *
     * @param array  $page   The page details.
     *
     * @param string $itemId The id of the item.
     *
     * @return string
     */
    private function generateCreateVariantUrl(array $page, $itemId): string
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL);

        $url = UrlBuilder::fromUrl($event->getUrl() . '?')
            ->setQueryParameter('act', 'createvariant')
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
    private function generateDeleteUrl(array $page, $itemId): string
    {
        $event = new GenerateFrontendUrlEvent($page, null, $page['language']);

        $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL);

        $url = UrlBuilder::fromUrl($event->getUrl() . '?')
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
    private function getPageDetails($pageId): ?array
    {
        if (empty($pageId)) {
            return null;
        }
        $event = new GetPageDetailsEvent($pageId);
        $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GET_PAGE_DETAILS);

        return $event->getPageDetails();
    }

    /**
     * Get a translated label from the translator.
     *
     * The fallback is as follows:
     * 1. Try to translate via the data definition name as translation section.
     * 2. Try to translate with the prefix 'MSC.'.
     * 3. Return the input value as nothing worked out.
     *
     * @param string $transString    The non translated label for the button.
     *
     * @param string $definitionName The data definition of the current item.
     *
     * @param array  $parameters     The parameters to pass to the translator.
     *
     * @return string
     */
    private function translateLabel($transString, $definitionName, array $parameters = []): string
    {
        $translator = $this->translator;

        $label = $translator->trans($definitionName . '.' . $transString, $parameters, 'contao_' . $definitionName);
        if ($label !== $definitionName . '.' . $transString) {
            return $label;
        }

        $label = $translator->trans('MSC.' . $transString, $parameters, 'contao_default');
        if ($label !== $transString) {
            return $label;
        }

        // Fallback, just return the key as is it.
        return $transString;
    }
}
