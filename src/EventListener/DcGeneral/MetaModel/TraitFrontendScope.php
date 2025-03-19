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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;

/**
 * This is for detected the metamodel is in the frontend scope.
 */
trait TraitFrontendScope
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    private function wantToHandle(AbstractEnvironmentAwareEvent $event): bool
    {
        $scopeDeterminator = $this->scopeDeterminator;
        assert($scopeDeterminator instanceof RequestScopeDeterminator);

        if (!$scopeDeterminator->currentScopeIsFrontend()) {
            return false;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();

        if (!($dataDefinition instanceof IMetaModelDataDefinition)) {
            return false;
        }

        if (
            ($event instanceof AbstractModelAwareEvent)
            && ($dataDefinition->getName() !== $event->getModel()->getProviderName())
        ) {
            return false;
        }

        return true;
    }
}
