<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\Notification;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This send notification, if a model edited.
 */
final class EditModelNotificationListener extends AbstractNotification
{
    /**
     * {@inheritDoc}
     */
    protected function actionName(): string
    {
        return 'edit';
    }

    /**
     * {@inheritDoc}
     */
    protected function metaName(): string
    {
        return 'fe_edit_notification';
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException When the wrong event loaded.
     */
    protected function generateOptionalToken(AbstractEnvironmentAwareEvent $event, string $flattenDelimiter): array
    {
        if (!($event instanceof PostPersistModelEvent)) {
            throw new DcGeneralRuntimeException(
                'The wrong event is called ' . \get_class($event) .
                ', the accepted event is ' . PostPersistModelEvent::class . '.'
            );
        }

        return $this->generateTokensFromModel($event, $flattenDelimiter, 'model_original_', $event->getOriginalModel());
    }
}
