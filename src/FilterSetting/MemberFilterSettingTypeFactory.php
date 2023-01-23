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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\AbstractFilterSettingTypeFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Attribute type factory for published filter settings.
 */
final class MemberFilterSettingTypeFactory extends AbstractFilterSettingTypeFactory
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder
     */
    private FilterUrlBuilder $filterUrlBuilder;

    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * @var ContaoFramework
     * The security.
     *
     * @var Security
     */
    private Security $security;


    /**
     * Create a new instance.
     *
     * @param Connection               $connection        The database connection.
     * @param EventDispatcherInterface $dispatcher        The event dispatcher.
     * @param FilterUrlBuilder         $filterUrlBuilder  The filter URL builder.
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param Security                 $security          The security.
     */
    public function __construct(
        Connection $connection,
        EventDispatcherInterface $dispatcher,
        FilterUrlBuilder $filterUrlBuilder,
        RequestScopeDeterminator $scopeDeterminator,
        Security $security
    ) {
        parent::__construct();

        $this
            ->setTypeName('member_filter')
            ->setTypeIcon('bundles/metamodelscontaofrontendediting/filter_member.png')
            ->setTypeClass(MemberFilter::class)
            ->allowAttributeTypes('select');

        $this->connection        = $connection;
        $this->dispatcher        = $dispatcher;
        $this->filterUrlBuilder  = $filterUrlBuilder;
        $this->scopeDeterminator = $scopeDeterminator;
        $this->security          = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $filterSettings)
    {
        return new MemberFilter(
            $filterSettings,
            $information,
            $this->connection,
            $this->dispatcher,
            $this->filterUrlBuilder,
            $this->scopeDeterminator,
            $this->security
        );
    }
}
