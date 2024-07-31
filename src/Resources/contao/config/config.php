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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Mini Model <minimodel@metamodel.me>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use Contao\System;
use MetaModels\ContaoFrontendEditingBundle\Content\ModelEdit as ContentModelEdit;
use MetaModels\ContaoFrontendEditingBundle\Module\ModelEdit as ModuleModelEdit;

$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendediting'] = ModuleModelEdit::class;
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendediting'] = ContentModelEdit::class;

if (\in_array('notification_center', (array) System::getContainer()->getParameter('kernel.bundles'), true)) {
    $configCreateSimpleTokens = ['model_*', 'member_*', 'property_label_*' , 'data', 'admin_email'];
    $configEditSimpleTokens   = ['model_*', 'model_original_*', 'member_*', 'property_label_*' , 'data', 'admin_email'];
    $configDeleteSimpleTokens = ['model_*', 'member_*', 'property_label_*' , 'data', 'admin_email'];

    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] = \array_merge(
        (array) $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'],
        [
            'metamodels_frontendediting' => [
                'mm_fe_create_model' => [
                    'recipients'           => $configCreateSimpleTokens,
                    'email_subject'        => $configCreateSimpleTokens,
                    'email_text'           => $configCreateSimpleTokens,
                    'email_html'           => $configCreateSimpleTokens,
                    'file_name'            => $configCreateSimpleTokens,
                    'file_content'         => $configCreateSimpleTokens,
                    'email_sender_name'    => $configCreateSimpleTokens,
                    'email_sender_address' => $configCreateSimpleTokens,
                    'email_recipient_cc'   => $configCreateSimpleTokens,
                    'email_recipient_bcc'  => $configCreateSimpleTokens,
                    'email_replyTo'        => $configCreateSimpleTokens,
                    'attachment_tokens'    => $configCreateSimpleTokens
                ],
                'mm_fe_edit_model' => [
                    'recipients'           => $configEditSimpleTokens,
                    'email_subject'        => $configEditSimpleTokens,
                    'email_text'           => $configEditSimpleTokens,
                    'email_html'           => $configEditSimpleTokens,
                    'file_name'            => $configEditSimpleTokens,
                    'file_content'         => $configEditSimpleTokens,
                    'email_sender_name'    => $configEditSimpleTokens,
                    'email_sender_address' => $configEditSimpleTokens,
                    'email_recipient_cc'   => $configEditSimpleTokens,
                    'email_recipient_bcc'  => $configEditSimpleTokens,
                    'email_replyTo'        => $configEditSimpleTokens,
                    'attachment_tokens'    => $configEditSimpleTokens
                ],
                'mm_fe_copy_model' => [
                    'recipients'           => $configCreateSimpleTokens,
                    'email_subject'        => $configCreateSimpleTokens,
                    'email_text'           => $configCreateSimpleTokens,
                    'email_html'           => $configCreateSimpleTokens,
                    'file_name'            => $configCreateSimpleTokens,
                    'file_content'         => $configCreateSimpleTokens,
                    'email_sender_name'    => $configCreateSimpleTokens,
                    'email_sender_address' => $configCreateSimpleTokens,
                    'email_recipient_cc'   => $configCreateSimpleTokens,
                    'email_recipient_bcc'  => $configCreateSimpleTokens,
                    'email_replyTo'        => $configCreateSimpleTokens,
                    'attachment_tokens'    => $configCreateSimpleTokens
                ],
                'mm_fe_delete_model' => [
                    'recipients'           => $configDeleteSimpleTokens,
                    'email_subject'        => $configDeleteSimpleTokens,
                    'email_text'           => $configDeleteSimpleTokens,
                    'email_html'           => $configDeleteSimpleTokens,
                    'file_name'            => $configDeleteSimpleTokens,
                    'file_content'         => $configDeleteSimpleTokens,
                    'email_sender_name'    => $configDeleteSimpleTokens,
                    'email_sender_address' => $configDeleteSimpleTokens,
                    'email_recipient_cc'   => $configDeleteSimpleTokens,
                    'email_recipient_bcc'  => $configDeleteSimpleTokens,
                    'email_replyTo'        => $configDeleteSimpleTokens,
                    'attachment_tokens'    => $configDeleteSimpleTokens
                ]
            ]
        ]
    );

    unset($configCreateSimpleTokens, $configEditSimpleTokens, $configDeleteSimpleTokens);
}
