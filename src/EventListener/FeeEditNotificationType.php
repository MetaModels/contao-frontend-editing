<?php

namespace MetaModels\ContaoFrontendEditingBundle\EventListener;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

/**
 *
 */
final class FeeEditNotificationType implements NotificationTypeInterface
{
    use NotificationTypeTrait {
        getTokenDefinitions as traitGetTokenDefinitions;
    }

    /** @psalm-suppress MissingClassConstType */
    public const NAME = 'mm_fe_model_edit';

    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(AnythingTokenDefinition::class, 'model_original_*', 'mm_fe.model_original_*'),
            ...$this->traitGetTokenDefinitions(),
        ];
    }
}
