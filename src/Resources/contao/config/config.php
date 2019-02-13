<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2016-2019 The MetaModels team.
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
 * @copyright  2016-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

use MetaModels\ContaoFrontendEditingBundle\Content\ModelEdit as ContentModelEdit;
use MetaModels\ContaoFrontendEditingBundle\Module\ModelEdit as ModuleModelEdit;

$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendediting'] = ModuleModelEdit::class;
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendediting'] = ContentModelEdit::class;
