<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\NotificationCenter2x;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This send notification, if new model copied.
 */
final class CopyModelNotificationListener extends AbstractNotificationCenter
{
    /**
     * {@inheritDoc}
     */
    protected function actionName(): string
    {
        return 'copy';
    }

    /**
     * {@inheritDoc}
     */
    protected function metaName(): string
    {
        return 'fe_copy_notification';
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException When the wrong event loaded.
     */
    protected function generateOptionalToken(AbstractEnvironmentAwareEvent $event, string $flattenDelimiter): array
    {
        if (!($event instanceof PostDuplicateModelEvent)) {
            throw new DcGeneralRuntimeException(
                'The wrong event is called ' . \get_class($event) .
                ', the accepted event is ' . PostDuplicateModelEvent::class . '.'
            );
        }

        return $this->generateTokensFromModel($event, $flattenDelimiter, 'model_source_', $event->getSourceModel());
    }
}
