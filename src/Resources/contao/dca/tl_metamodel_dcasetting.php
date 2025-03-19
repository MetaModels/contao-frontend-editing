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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['fields'] = array_merge(
    (array) $GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['fields'],
    [
        'fe_template' => [
            'label'       => 'fe_template.label',
            'description' => 'fe_template.description',
            'exclude'   => true,
            'inputType' => 'select',
            'sql'       => 'varchar(255) NOT NULL default \'\'',
            'eval'      => [
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'chosen'             => 'true'
            ]
        ]
    ]
);
