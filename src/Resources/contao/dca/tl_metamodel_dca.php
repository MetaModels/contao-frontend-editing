<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

if (\array_key_exists('notification_center', \Contao\System::getContainer()->getParameter('kernel.bundles'))) {
    $GLOBALS['TL_DCA']['tl_metamodel_dca']['metapalettes']['default']['fe_notification'] = [
        'fe_create_notification',
        'fe_edit_notification',
        'fe_copy_notification',
        'fe_delete_notification'
    ];

    $GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'] = array_merge(
        (array) $GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'],
        [
            'fe_create_notification' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_create_notification'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_edit_notification' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_edit_notification'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_copy_notification' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_copy_notification'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_delete_notification' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_delete_notification'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ]
    );
}
