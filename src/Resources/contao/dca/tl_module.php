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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Mini Model <minimodel@metamodel.me>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list'] = str_replace(
    '{protected_legend:hide}',
    '{mm_editing_legend},metamodel_fe_editing;{protected_legend:hide}',
    $GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list']
);

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodels_frontendediting'] =
    '{title_legend},name,headline,type;' .
    '{mm_config_legend},metamodel;' .
    '{template_legend:hide},customTpl,metamodel_fe_editing_jshelper;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,invisible,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]          = 'metamodel_fe_editing';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['metamodel_fe_editing'] = 'metamodel_fe_editing_page';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]                   = 'metamodel_fe_editing_jshelper';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['metamodel_fe_editing_jshelper'] = 'metamodel_fe_editing_csshelper';

$GLOBALS['TL_DCA']['tl_module']['fields']['metamodel_fe_editing'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fe_editing'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'char(1) NOT NULL default \'\'',
    'eval'      => [
        'submitOnChange' => true,
    ]
];

$GLOBALS['TL_DCA']['tl_module']['fields']['metamodel_fe_editing_page'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fe_editing_page'],
    'exclude'   => true,
    'inputType' => 'pageTree',
    'sql'       => 'int(10) unsigned NOT NULL default \'0\'',
    'eval'      => [
        'fieldType' => 'radio'
    ]
];

$GLOBALS['TL_DCA']['tl_module']['fields']['customTpl'] = [
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => static function () {
        return Contao\Controller::getTemplateGroup('mod_metamodel_frontend_edit_', [], 'mod_metamodel_frontend_edit');
    },
    'eval'             => [
        'tl_class' => 'clr w50',
        'chosen'   => true
    ],
    'sql'              => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['metamodel_fe_editing_jshelper'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fe_editing_jshelper'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'char(1) NOT NULL default \'\'',
    'eval'      => [
        'tl_class'       => 'clr w50 cbx m12',
        'submitOnChange' => true,
    ]
];

$GLOBALS['TL_DCA']['tl_module']['fields']['metamodel_fe_editing_csshelper'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fe_editing_csshelper'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'default'   => true,
    'sql'       => 'char(1) NOT NULL default \'1\'',
    'eval'      => [
        'tl_class' => 'w50 cbx m12',
    ]
];
