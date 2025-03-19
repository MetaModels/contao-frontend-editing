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
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\NotificationCenter2x;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * This send notification, if a model deleted.
 */
final class DeleteModelNotificationListener extends AbstractNotificationCenter
{
    /**
     * {@inheritDoc}
     */
    protected function actionName(): string
    {
        return 'delete';
    }

    /**
     * {@inheritDoc}
     */
    protected function metaName(): string
    {
        return 'fe_delete_notification';
    }

    /**
     * {@inheritDoc}
     */
    protected function generateOptionalToken(AbstractEnvironmentAwareEvent $event, string $flattenDelimiter): array
    {
        return [];
    }
}
