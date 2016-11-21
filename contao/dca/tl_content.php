<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage ContaoFrontendEditing
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2016 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodel_content'] = str_replace(
    '{protected_legend:hide}',
    '{mm_editing_legend},metamodel_fe_editing;{protected_legend:hide}',
    $GLOBALS['TL_DCA']['tl_content']['palettes']['metamodel_content']
);

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodels_frontendediting'] =
    '{type_legend},name,headline,type;' .
    '{mm_config_legend},metamodel;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,invisible,cssID,space';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'metamodel_fe_editing';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['metamodel_fe_editing'] = 'metamodel_fe_editing_page';

$GLOBALS['TL_DCA']['tl_content']['fields']['metamodel_fe_editing'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fe_editing'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'char(1) NOT NULL default \'\'',
    'eval'      => [
        'submitOnChange' => true,
    ]
];

$GLOBALS['TL_DCA']['tl_content']['fields']['metamodel_fe_editing_page'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fe_editing_page'],
    'exclude'   => true,
    'inputType' => 'pageTree',
    'sql'       => 'int(10) unsigned NOT NULL default \'0\'',
    'eval'      => [
        'fieldType' => 'radio'
    ]
];
