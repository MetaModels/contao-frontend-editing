<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Contao\System;
use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;
use MetaModels\IMetaModel;
use MetaModels\ViewCombination\InputScreenInformationBuilder;
use Symfony\Component\Security\Core\Security;

class EditMaskMemberPermissionListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * The security.
     *
     * @var Security
     */
    private Security $security;

    /**
     * The input screen information builder.
     *
     * @var InputScreenInformationBuilder
     */
    private InputScreenInformationBuilder $inputScreens;

    /**
     * The name of member attribute eq. column name.
     *
     * @var string
     */
    private string $memberAttribut;

    /**
     * EditMaskMemberPermissionListener constructor.
     *
     * @param RequestScopeDeterminator      $scopeDeterminator The scope determinator.
     * @param Security                      $security          The security.
     * @param InputScreenInformationBuilder $inputScreens      The input screen information builder.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        Security $security,
        InputScreenInformationBuilder $inputScreens
    ) {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->security          = $security;
        $this->inputScreens      = $inputScreens;
        $this->memberAttribut    = '';
    }

    public function saveMemberId(PrePersistModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        // Only if created.
        $model = $event->getModel();
        if (null !== $model->getId()) {
            return;
        }

        // Get member.
        $user = $this->security->getUser();
        if (!$user instanceof FrontendUser) {
            return;
        }

        // Set data.
        $model->setProperty($this->memberAttribut, $user->getUserIdentifier());
    }

    /**
     * Check if editable for member (frontend user) as item of relation of account.
     *
     * @param PreEditModelEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkPreEditFee(PreEditModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        // Check user permission.
        $this->checkUserPermission($event);
    }

    /**
     * Check if savable for member (frontend user) as item of relation of account.
     *
     * @param PrePersistModelEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkSaveFee(PrePersistModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        // Check user permission.
        $this->checkUserPermission($event);
    }

    /**
     * Check if duplicatable for member (frontend user) as item of relation of account.
     *
     * @param PreDuplicateModelEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkDuplicateFee(PreDuplicateModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        // Check user permission.
        $this->checkUserPermission($event);
    }

    /**
     * Check if deletable for member (frontend user) as item of relation of account.
     *
     * @param PreDeleteModelEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkDeleteFee(PreDeleteModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        // Check user permission.
        $this->checkUserPermission($event);
    }

    /**
     * Check permission for member (frontend user) as item of relation of account.
     *
     * @param AbstractModelAwareEvent $event The event to check.
     *
     * @return void
     *
     * @throws AccessDeniedException
     */
    private function checkUserPermission(AbstractModelAwareEvent $event)
    {
        // Check if 'duplicate' - else all other.
        if ($event instanceof PreDuplicateModelEvent) {
            $model = $event->getSourceModel();
        } else {
            $model = $event->getModel();
        }

        // If model id null the type is 'create' - else is 'edit' or 'delete'.
        if (null === $model->getId()) {
            return;
        }

        // Get member.
        $user = $this->security->getUser();
        if (!$user instanceof FrontendUser) {
            return;
        }

        // If type 'edit' or 'delete' check the permission.
        $itemValue = $model->getItem()->parseAttribute($this->memberAttribut, 'text');
        $username  = $itemValue['raw']['username'] ?? '';

        // Check if same account id to open and edit the item.
        if ($username === $user->getUserIdentifier()) {
            return;
        }

        // Error 403.
        throw new AccessDeniedException('Not your item!');
    }

    /**
     * Check if the event is intended for us.
     *
     * @param $event AbstractModelAwareEvent The event to test.
     *
     * @return bool
     */
    private function wantToHandle(AbstractModelAwareEvent $event)
    {
        // Check is frontend.
        if (!$this->scopeDeterminator->currentScopeIsFrontend()) {
            return false;
        }

        // Retrieve the settings of the input mask for member attribute.
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        /** @var IMetaModelDefinition $metaModel */
        $metaModel     = $dataDefinition->getDefinition(IMetaModelDefinition::NAME);
        $metaModelName = $dataDefinition->getName();
        $screen        = $this->inputScreens->fetchInputScreens([$metaModelName => $metaModel->getActiveInputScreen()]);
        $screenMeta    = $screen[$metaModelName]['meta'];

        if (!empty($screenMeta['fe_useMemberPermissions']) && !empty($screenMeta['fe_memberAttribut'])) {
            $this->memberAttribut = $screenMeta['fe_memberAttribut'];

            return true;
        }

        return false;
    }
}
