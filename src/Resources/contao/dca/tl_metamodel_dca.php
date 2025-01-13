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

$GLOBALS['TL_DCA']['tl_metamodel_dca']['metapalettes']['default']['fe_editButtons'] = [
    'fe_overrideEditButtons'
];

$GLOBALS['TL_DCA']['tl_metamodel_dca']['metasubpalettes']['fe_overrideEditButtons'] = [
    'fe_editButtons'
];

$GLOBALS['TL_DCA']['tl_metamodel_dca']['metapalettes']['default']['fe_memberAttribut'] = [
    'fe_useMemberPermissions'
];

$GLOBALS['TL_DCA']['tl_metamodel_dca']['metasubpalettes']['fe_useMemberPermissions'] = [
    'fe_memberAttribut'
];

$GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'] = \array_merge(
    (array) $GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'],
    [
        'fe_overrideEditButtons'  => [
            'label'       => 'fe_overrideEditButtons.label',
            'description' => 'fe_overrideEditButtons.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'sql'         => ['type' => 'boolean', 'notnull' => false],
            'eval'        => [
                'submitOnChange' => true,
                'tl_class'       => 'w50 cbx'
            ]
        ],
        'fe_editButtons'          => [
            'label'       => 'fe_editButtons.label',
            'description' => 'fe_editButtons.description',
            'exclude'     => true,
            'inputType'   => 'multiColumnWizard',
            'default'     => [
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
            'sql'         => ['type' => 'array', 'notnull' => false],
            'eval'        => [
                'useTranslator' => true,
                'tl_class'      => 'clr',
                'columnFields'  => [
                    'name'            => [
                        'label'       => 'fe_editButtons_name.label',
                        'description' => 'fe_editButtons_name.description',
                        'exclude'     => true,
                        'inputType'   => 'text',
                        'eval'        => [
                            'rgxp'      => 'alias',
                            'mandatory' => true,
                            'style'     => 'width: 100%;'
                        ]
                    ],
                    'label'           => [
                        'label'       => 'fe_editButtons_label.label',
                        'description' => 'fe_editButtons_label.description',
                        'exclude'     => true,
                        'inputType'   => 'text',
                        'eval'        => [
                            'style' => 'width: 100%;'
                        ]
                    ],
                    'attributes'      => [
                        'label'       => 'fe_editButtons_attributes.label',
                        'description' => 'fe_editButtons_attributes.description',
                        'exclude'     => true,
                        'inputType'   => 'text',
                        'eval'        => [
                            'style' => 'width:100%;'
                        ]
                    ],
                    'jumpTo'          => [
                        'label'       => 'fe_editButtons_jumpTo.label',
                        'description' => 'fe_editButtons_jumpTo.description',
                        'exclude'     => true,
                        // @codingStandardsIgnoreStart
                        // FIXME: Use page tree if this work with mcw.
                        // @codingStandardsIgnoreEnd
                        'inputType'   => 'text',
                        'eval'        => [
                            'fieldType' => 'radio',
                            'dcaPicker' => ['providers' => ['pagePicker']],
                            'tl_class'  => 'w50 wizard'
                        ]
                    ],
                    'jumpToParameter' => [
                        'label'       => 'fe_editButtons_jumpToParameter.label',
                        'description' => 'fe_editButtons_jumpToParameter.description',
                        'exclude'     => true,
                        'inputType'   => 'text',
                        'eval'        => [
                            'style' => 'width: 100%;'
                        ]
                    ],
                    'notSave'         => [
                        'label'       => 'fe_editButtons_notSave.label',
                        'description' => 'fe_editButtons_notSave.description',
                        'exclude'     => true,
                        'inputType'   => 'checkbox'
                    ]
                ]
            ]
        ],
        'fe_useMemberPermissions' => [
            'label'       => 'fe_useMemberPermissions.label',
            'description' => 'fe_useMemberPermissions.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'sql'         => ['type' => 'boolean', 'notnull' => false],
            'eval'        => [
                'submitOnChange' => true,
                'tl_class'       => 'w50 cbx'
            ]
        ],
        'fe_memberAttribut'       => [
            'label'       => 'fe_memberAttribut.label',
            'description' => 'fe_memberAttribut.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'sql'         => 'varchar(255) NOT NULL default \'\'',
            'eval'        => [
                'includeBlankOption' => true,
                'mandatory'          => true,
                'tl_class'           => 'clr w50',
                'chosen'             => 'true'
            ]
        ]
    ]
);

$bundles = System::getContainer()->getParameter('kernel.bundles');
assert(\is_array($bundles));

if (\array_key_exists('notification_center', $bundles) || \array_key_exists('Terminal42NotificationCenterBundle', $bundles)) {
    $GLOBALS['TL_DCA']['tl_metamodel_dca']['metapalettes']['default']['fe_notification'] = [
        'fe_create_notification',
        'fe_edit_notification',
        'fe_copy_notification',
        'fe_delete_notification'
    ];

    $GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'] = \array_merge(
        (array) $GLOBALS['TL_DCA']['tl_metamodel_dca']['fields'],
        [
            'fe_create_notification' => [
                'label'       => 'fe_create_notification.label',
                'description' => 'fe_create_notification.description',
                'exclude'     => true,
                'inputType'   => 'select',
                'eval'        => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
                'sql'         => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_edit_notification' => [
                'label'       => 'fe_edit_notification.label',
                'description' => 'fe_edit_notification.description',
                'exclude'     => true,
                'inputType'   => 'select',
                'eval'        => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
                'sql'         => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_copy_notification' => [
                'label'       => 'fe_copy_notification.label',
                'description' => 'fe_copy_notification.description',
                'exclude'     => true,
                'inputType'   => 'select',
                'eval'        => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
                'sql'         => ['type' => 'integer', 'notnull' => false]
            ]
        ],
        [
            'fe_delete_notification' => [
                'label'       => 'fe_delete_notification.label',
                'description' => 'fe_delete_notification.description',
                'exclude'     => true,
                'inputType'   => 'select',
                'eval'        => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
                'sql'         => ['type' => 'integer', 'notnull' => false]
            ]
        ]
    );
}
