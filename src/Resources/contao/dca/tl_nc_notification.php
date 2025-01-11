<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use Contao\System;

$bundles = System::getContainer()->getParameter('kernel.bundles');
assert(\is_array($bundles));

if (\array_key_exists('notification_center', $bundles)) {
    $GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['mm_fe_create_model'] =
        '{title_legend},title,type;' .
        '{config_legend},flatten_delimiter;' .
        '{templates_legend:hide},templates';
    $GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['mm_fe_edit_model']   =
        '{title_legend},title,type;' .
        '{config_legend},flatten_delimiter;' .
        '{templates_legend:hide},templates';
    $GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['mm_fe_copy_model']   =
        '{title_legend},title,type;' .
        '{config_legend},flatten_delimiter;' .
        '{templates_legend:hide},templates';
    $GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['mm_fe_delete_model'] =
        '{title_legend},title,type;' .
        '{config_legend},flatten_delimiter;' .
        '{templates_legend:hide},templates';
}
