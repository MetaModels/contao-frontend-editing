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

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use MetaModels\Attribute\IInternal;
use MetaModels\DcGeneral\Data\Model;

final class ManipulateWidgetListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * Change the widget template with your own choice.
     *
     * @param ManipulateWidgetEvent $event
     *
     * @return void
     */
    public function __invoke(ManipulateWidgetEvent $event): void
    {
        if (!$this->scopeDeterminator->currentScopeIsFrontend()) {
            return;
        }

        $model = $event->getModel();
        if (!$model instanceof Model) {
            return;
        }

        $property = $event->getProperty();
        if (null === $attribute = $model->getItem()->getMetaModel()->getAttribute($property->getName())) {
            return;
        }

        // Check virtual types.
        if ($attribute instanceof IInternal) {
            return;
        }

        if (!\in_array('fe_template', $attribute->getAttributeSettingNames(), true)) {
            return;
        }

        $propExtra = $property->getExtra();

        if (null !== ($template = $propExtra['fe_template'] ?? null)) {
            $event->getWidget()->template = $template;
        }
    }
}
