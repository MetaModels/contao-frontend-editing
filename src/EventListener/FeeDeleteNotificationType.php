<?php

namespace MetaModels\ContaoFrontendEditingBundle\EventListener;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;

final class FeeDeleteNotificationType implements NotificationTypeInterface
{
    use NotificationTypeTrait;

    /** @psalm-suppress MissingClassConstType */
    public const NAME = 'mm_fe_model_delete';
}
