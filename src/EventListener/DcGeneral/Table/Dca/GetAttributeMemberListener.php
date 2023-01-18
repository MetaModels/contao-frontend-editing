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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\Table\Dca;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use MetaModels\AttributeSelectBundle\Attribute\AbstractSelect;
use MetaModels\Attribute\ISimple;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;

class GetAttributeMemberListener
{
    /**
     * Request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * Metamodels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * GetOptionsListener constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator Request scope determinator.
     * @param IFactory                 $factory           Metamodels factory.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, IFactory $factory)
    {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->factory           = $factory;
    }

    /**
     * Check if the event is intended for us.
     *
     * @param GetOptionsEvent $event The event to test.
     *
     * @return bool
     */
    private function wantToHandle(GetPropertyOptionsEvent $event)
    {
        if (false === $this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        return
            ($event->getEnvironment()->getDataDefinition()->getName() === 'tl_metamodel_dca')
            && ($event->getPropertyName() === 'fe_memberAttribut');
    }

    /**
     * Retrieve the property options.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getPropertyOptions(GetPropertyOptionsEvent $event)
    {
        if (null !== $event->getOptions() || !$this->wantToHandle($event)) {
            return;
        }

        $model       = $event->getModel();
        $metaModelId = $model->getProperty('pid');
        if (!$metaModelId) {
            $metaModelId = ModelId::fromSerialized(
                $event->getEnvironment()->getInputProvider()->getParameter('pid')
            )->getId();
        }

        $metaModelName = $this->factory->translateIdToMetaModelName($metaModelId);
        $metaModel     = $this->factory->getMetaModel($metaModelName);

        if (!$metaModel) {
            return;
        }

        $result = [];

        // Fetch all attributes except for the current attribute.
        foreach ($metaModel->getAttributes() as $attribute) {
            // Show only select attributes with table 'tl_member' and alias 'username'.
            if ('select' === $attribute->get('type')
                && 'tl_member' === $attribute->get('select_table')
                && 'username' === $attribute->get('select_alias')) {
                $result[$attribute->getColName()] = \sprintf(
                    '%s [%s, "%s"]',
                    $attribute->getName(),
                    $attribute->get('type'),
                    $attribute->getColName()
                );
            }
        }

        $event->setOptions($result);
    }
}
