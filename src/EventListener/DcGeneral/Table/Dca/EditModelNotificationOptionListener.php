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

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\Table\Dca;

use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;

/**
 * This listener collect options for the property edit model notification.
 */
final class EditModelNotificationOptionListener extends AbstractNotificationOption
{
    /**
     * {@inheritDoc}
     */
    protected function propertyName(): string
    {
        return 'fe_edit_notification';
    }

    /**
     * {@inheritDoc}
     */
    protected function notificationType(): string
    {
        // Remove in MM 2.4
        if (!\class_exists(Terminal42NotificationCenterBundle::class, true)) {
            return 'mm_fe_edit_model';
        }
        return 'mm_fe_model_edit';
    }
}
