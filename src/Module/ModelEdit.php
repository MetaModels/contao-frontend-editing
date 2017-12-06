<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2016-2017 The MetaModels team.
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
 * @copyright  2016-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\Module;

use MetaModels\ContaoFrontendEditingBundle\FrontendEditHybrid;

/**
 * This class is the integration as module.
 */
class ModelEdit extends FrontendEditHybrid
{
    /**
     * The Template instance.
     *
     * @var string
     */
    protected $strTemplate = 'mod_metamodel_frontend_edit';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $wildCardLink = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=%s';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix = 'mod_';
}
