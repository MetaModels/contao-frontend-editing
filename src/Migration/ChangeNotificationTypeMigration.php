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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;
use function class_exists;

/**
 * Change notification types in "tl_nc_notification" for version 2.
 */
class ChangeNotificationTypeMigration extends AbstractMigration
{
    // Type list with old names and new names.
    private array $types = [
        'mm_fe_create_model' => 'mm_fe_model_create',
        'mm_fe_edit_model'   => 'mm_fe_model_edit',
        'mm_fe_copy_model'   => 'mm_fe_model_copy',
        'mm_fe_delete_model' => 'mm_fe_model_delete',
    ];

    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Change MM-FEE notification types in "tl_nc_notification" for NC version 2.x.';
    }

    /**
     * Must only run if:
     * - the NC 2.x installed
     * - table tl_nc_notification exist
     * - old type keys exist.
     *
     * @return bool
     * @throws Exception
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (
            class_exists(Terminal42NotificationCenterBundle::class, true)
            && $schemaManager->tablesExist(['tl_nc_notification'])
            && $this->checkNcTypesToChange()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Change notification types in tl_nc_notification.
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (
            !class_exists(Terminal42NotificationCenterBundle::class, true)
            || !$schemaManager->tablesExist(['tl_nc_notification'])
            || !$this->checkNcTypesToChange()
        ) {
            return new MigrationResult(false, '');
        }

        foreach ($this->types as $oldName => $newName) {
            $this->connection->createQueryBuilder()
                ->update('tl_nc_notification', 't')
                ->set('t.type', ':new_name')
                ->where('t.type=:old_name')
                ->setParameter('new_name', $newName)
                ->setParameter('old_name', $oldName)
                ->executeQuery();
        }

        return new MigrationResult(
            true, 'MM-FEE notification types in "tl_nc_notification" for NC version 2.x changed.'
        );
    }

    private function checkNcTypesToChange(): bool
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('t.type')
            ->from('tl_nc_notification', 't')
            ->where('REGEXP_INSTR(:pattern, t.type) = 1')
            ->setParameter('pattern', \implode(' ', \array_keys($this->types)))
            ->executeQuery()
            ->fetchFirstColumn();

        if (empty($statement)) {
            return false;
        }

        return true;
    }
}
