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
 * @package    MetaModels/contao-frontend-editing
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2016-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_overrideEditButtons'] = [
    'Override frontend edit buttons',
    'If is checked, so you can define the edit buttons.'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons'] = [
    'Frontend edit buttons',
    'Here can you define the edit buttons.'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_name'] = [
    'Name',
    'Here can you define the edit button name (e.g. "saveNCreate" for save and create).'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_label'] = [
    'Label',
    'Here can you optional define the label of the edit button. If is not defined, so will look automatically for a ' .
    'translation in TABLE_NAME.MSC.BUTTON_NAME'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_attributes'] = [
    'Attributes',
    'Here can you define additional attributes for edit button html tag (e.g. `accesskey="s"`).'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_jumpTo'] = [
    'Forwarding',
    'Here can you define the forward page.'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_jumpToParameter'] = [
    'Parameter',
    'Here can you define additional url parameter for the forward page.'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_notSave'] = [
    'Not save',
    'Here can you define the model not saved. If the button clicked.'
];

$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_create_notification'] = [
    'Create model',
    'This notification type sent when a new model is created.'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_edit_notification']   = [
    'Edit model',
    'This notification type sent when a model is edited.'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_copy_notification']   = [
    'Copy model',
    'This notification type sent when a model is copied.'
];
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_delete_notification'] = [
    'Delete model',
    'This notification type sent when a model is deleted.'
];

$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_editButtons_legend']  = 'Frontend edit buttons';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_notification_legend'] = 'Frontend notification';
