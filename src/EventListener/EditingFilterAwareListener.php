<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage ContaoFrontendEditing
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2017 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Contao\FrontendEditing\EventListener;

use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use MetaModels\Contao\FrontendEditing\FrontendEditHybrid;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\ItemList;

/**
 * This class handles …
 */
class EditingFilterAwareListener
{

    /**
     * Apply the filter before editing the model.
     *
     * @param PreEditModelEvent $event
     *
     * @return void
     */
    public function applyFilterOnModelEdit(PreEditModelEvent $event)
    {
        $environment = $event->getEnvironment();
        $caller      = $environment->getSessionStorage()->get('frontend-editor-caller');
        $model       = $event->getModel();

        if (!$caller instanceof FrontendEditHybrid || !$model instanceof Model) {
            return;
        }

        $item = $model->getItem();

        // The ItemList class will prepare and provide us a filter instance
        $itemList = new ItemList();
        $itemList
            ->setMetaModel($item->getMetaModel()->get('id'), 0)
            ->setFilterSettings($caller->metamodel_filtering)
            ->setFilterParameters([], []) //@todo
            ->prepare();
        $filter = $itemList->getFilter();

        $matchingIds = $filter->getMatchingIds();
        if (null !== $matchingIds && !in_array($item->get('id'), $matchingIds)) {
            throw new \Exception('Id not allowed'); //@todo
        }
    }
}
