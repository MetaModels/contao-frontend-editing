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
use Contao\FrontendUser;
use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\Filter\Setting\Simple;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Published setting handler for checkboxes.
 */
final class MemberFilter extends Simple
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * The security.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Constructor - initialize the object and store the parameters.
     *
     * @param ICollection                   $collection        The parenting filter settings object.
     * @param array                         $data              The attributes for this filter setting.
     * @param Connection                    $connection        The database connection.
     * @param EventDispatcherInterface      $dispatcher        The event dispatcher.
     * @param FilterUrlBuilder              $filterUrlBuilder  The filter URL builder.
     * @param RequestScopeDeterminator      $scopeDeterminator The scope determinator.
     * @param Security                      $security          The security.
     */
    public function __construct(
        ICollection $collection,
        array $data,
        Connection $connection,
        EventDispatcherInterface $dispatcher,
        FilterUrlBuilder $filterUrlBuilder,
        RequestScopeDeterminator $scopeDeterminator,
        Security $security
    ) {
        parent::__construct($collection, $data, $dispatcher, $filterUrlBuilder);

        $this->connection        = $connection;
        $this->scopeDeterminator = $scopeDeterminator;
        $this->security          = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $user      = $this->security->getUser();
        $attribute = $this->getMetaModel()->getAttributeById((int) $this->get('attr_id'));

        // Check frontend, user and attribute.
        if ($this->scopeDeterminator->currentScopeIsFrontend() && ($user instanceof FrontendUser) && $attribute) {
            $memberQuery = $this->connection->createQueryBuilder();
            $memberQuery
                ->select('m.id')
                ->from($attribute->get('select_table'), 'm')
                ->where('m.' . $attribute->get('select_alias') . '=:identifyer')
                ->setMaxResults(1);

            $objFilter->addFilterRule(
                SimpleQuery::createFromQueryBuilder(
                    $this
                        ->connection
                        ->createQueryBuilder()
                        ->select('t.id')
                        ->from($this->getMetaModel()->getTableName(), 't')
                        ->where('t.' . $attribute->getColName() . '=(' . $memberQuery->getSQL() . ')')
                        ->setParameter('identifyer', $user->getUserIdentifier())
                )
            );

            return;
        }

        // No attribute found, do not return anything.
        $objFilter->addFilterRule(new StaticIdList([]));
    }
}
