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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\AttributeSelectBundle\Attribute\AbstractSelect;
use MetaModels\Attribute\ISimple;
use MetaModels\CoreBundle\Formatter\SelectAttributeOptionLabelFormatter;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GetAttributeMemberListener
{
    /**
     * Request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterFactory;

    /**
     * The attribute select option label formatter.
     *
     * @var SelectAttributeOptionLabelFormatter
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private SelectAttributeOptionLabelFormatter $attributeLabelFormatter;

    /**
     * GetOptionsListener constructor.
     *
     * @param RequestScopeDeterminator            $scopeDeterminator       Request scope determinator.
     * @param IFilterSettingFactory               $filterFactory           The filter setting factory.
     * @param SelectAttributeOptionLabelFormatter $attributeLabelFormatter The attribute select option label formatter.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFilterSettingFactory $filterFactory,
        SelectAttributeOptionLabelFormatter $attributeLabelFormatter
    ) {
        $this->scopeDeterminator       = $scopeDeterminator;
        $this->filterFactory           = $filterFactory;
        $this->attributeLabelFormatter = $attributeLabelFormatter;
    }

    /**
     * Check if the event is intended for us.
     *
     * @param GetPropertyOptionsEvent $event The event to test.
     *
     * @return bool
     */
    private function wantToHandle(GetPropertyOptionsEvent $event): bool
    {
        if (false === $this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        return
            (
                $dataDefinition->getName() === 'tl_metamodel_filtersetting'
                && ($event->getModel()->getProperty('type') === 'member_filter')
                && ($event->getPropertyName() === 'attr_id')
            );
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
    public function getPropertyOptions(GetPropertyOptionsEvent $event): void
    {
        if (null !== $event->getOptions() || !$this->wantToHandle($event)) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->filterFactory->createCollection($model->getProperty('fid'))->getMetaModel();

        $result = [];
        $prefix = ($event->getPropertyName() === 'attr_id') ? $metaModel->getTableName() . '_' : '';
        // Fetch all attributes except for the current attribute.
        foreach ($metaModel->getAttributes() as $attribute) {
            // Show only select attributes with table 'tl_member' and alias 'username'.
            if (
                'select' === $attribute->get('type')
                && 'tl_member' === $attribute->get('select_table')
                && 'username' === $attribute->get('select_alias')
            ) {
                $strSelectVal          = $prefix . $attribute->getColName();
                $result[$strSelectVal] = $this->attributeLabelFormatter->formatLabel($attribute);
            }
        }

        $event->setOptions($result);
    }
}
