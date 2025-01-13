<?php

namespace MetaModels\ContaoFrontendEditingBundle\EventListener;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

/**
 * @require-implements NotificationTypeInterface
 */
trait NotificationTypeTrait
{
    public function __construct(
        private readonly TokenDefinitionFactoryInterface $factory,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /** @return list<TokenDefinitionInterface> */
    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(AnythingTokenDefinition::class, 'model_*', 'mm_fe.model_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_*', 'mm_fe.member_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'property_label_*', 'mm_fe.property_label_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'data', 'mm_fe.data'),
            $this->factory->create(EmailTokenDefinition::class, 'admin_email', 'admin_email'),
        ];
    }
}
