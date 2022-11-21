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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dca']['metapalettes']['default']['fe_editButtons'] = [
    'fe_overrideEditButtons'
];

$GLOBALS['TL_DCA']['tl_metamodel_dca']['metasubpalettes']['fe_overrideEditButtons'] = [
    'fe_editButtons'
];

$GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'] = array_merge(
    (array) $GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'],
    [
        'fe_overrideEditButtons' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_overrideEditButtons'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'sql'       => ['type' => 'boolean', 'notnull' => false],
            'eval'      => [
                'submitOnChange' => true,
                'tl_class' => 'w50 cbx'
            ]
        ],
        'fe_editButtons' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'default' => [
                [
                    'name'       => 'save',
                    'label'      => 'MSC.save',
                    'attributes' => 'accesskey="s"'
                ],
                [
                    'name'       => 'saveNcreate',
                    'label'      => 'MSC.saveNcreate',
                    'attributes' => 'accesskey="n"'
                ]
            ],
            'sql'       => ['type' => 'array', 'notnull' => false],
            'eval'      => [
                'tl_class'     => 'clr',
                'columnFields' => [
                    'name'   => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_name'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'rgxp'      => 'fieldname',
                            'style'     => 'width: 100%;'
                        ]
                    ],
                    'label'   => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_label'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'rgxp'   => 'alias',
                            'style'  => 'width: 100%;'
                        ]
                    ],
                    'attributes' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_attributes'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'style' => 'width:100%;'
                        ]
                    ],
                    'jumpTo' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_jumpTo'],
                        'exclude'    => true,
                        // @codingStandardsIgnoreStart
                        // FIXME: Use page tree if this work with mcw.
                        // @codingStandardsIgnoreEnd
                        'inputType' => 'text',
                        'eval'      => [
                            'fieldType' => 'radio',
                            'dcaPicker' => ['providers' => ['pagePicker']],
                            'tl_class'  => 'w50 wizard'
                        ]
                    ],
                    'jumpToParameter' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_jumpToParameter'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'style' => 'width: 100%;'
                        ]
                    ],
                    'notSave' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_notSave'],
                        'exclude'   => true,
                        'inputType' => 'checkbox'
                    ]
                ]
            ]
        ]
    ]
);

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
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_edit_notification' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_edit_notification'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_copy_notification' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_copy_notification'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_delete_notification' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_delete_notification'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
                'sql'       => ['type' => 'integer', 'notnull' => false]
            ]
        ]
    );
}
