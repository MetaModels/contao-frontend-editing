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

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use Doctrine\DBAL\Connection;

/**
 * This is for collect options for the notification property.
 */
abstract class AbstractNotificationOption
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The database connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The constructor.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the property options.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function __invoke(GetPropertyOptionsEvent $event): void
    {
        if (!$this->wantToHandle($event) || ($this->propertyName() !== $event->getPropertyName())) {
            return;
        }

        $event->setOptions($this->collectPropertyOptions());
    }

    /**
     * Collect the options for the property.
     *
     * @return array
     */
    private function collectPropertyOptions(): array
    {
        $platform = $this->connection->getDatabasePlatform();

        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select('t.id, t.title')
            ->from('tl_nc_notification', 't')
            ->where('t.type=:type')
            ->setParameter('type', $this->notificationType())
            ->orderBy('t.title');

        $statement = $builder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $result = $statement->fetchAll(\PDO::FETCH_OBJ);

        $options = [];
        foreach ($result as $item) {
            $options[$item->id] = \sprintf(
                '%s [%s]',
                $item->title,
                $item->id
            );
        }

        return $options;
    }

    /**
     * The property name.
     *
     * @return string
     */
    abstract protected function propertyName(): string;

    /**
     * The notification type.
     *
     * @return string
     */
    abstract protected function notificationType(): string;

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    private function wantToHandle(AbstractEnvironmentAwareEvent $event): bool
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        if ('tl_metamodel_dca' !== $event->getEnvironment()->getDataDefinition()->getName()) {
            return false;
        }

        if (($event instanceof AbstractModelAwareEvent)
            && ($event->getEnvironment()->getDataDefinition()->getName() !== $event->getModel()->getProviderName())
        ) {
            return false;
        }

        return true;
    }
}
