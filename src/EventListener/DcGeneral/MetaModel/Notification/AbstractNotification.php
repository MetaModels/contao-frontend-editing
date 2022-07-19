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

declare(strict_types=1);

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\Notification;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\FrontendUser;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\MetaModel\TraitFrontendScope;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\ViewCombination\ViewCombination;
use NotificationCenter\Model\Notification;
use Symfony\Contracts\HttpFoundation\RequestStack;
use Symfony\Contracts\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This is for send notification.
 */
abstract class AbstractNotification
{
    use TraitFrontendScope;

    /**
     * The view combination.
     *
     * @var ViewCombination
     */
    protected $viewCombination;

    /**
     * The notification center.
     *
     * @var Adapter|Notification
     */
    protected $notificationCenter;

    /**
     * The token storage.
     *
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The config service.
     *
     * @var Adapter|Config
     */
    private $config;

    /**
     * The constructor.
     *
     * @param ViewCombination       $viewCombination    The view combination.
     * @param Adapter               $notificationCenter The notification center.
     * @param TokenStorageInterface $tokenStorage       The token storage.
     * @param RequestStack          $requestStack       The request stack.
     * @param Adapter               $config             The config service.
     */
    public function __construct(
        ViewCombination $viewCombination,
        Adapter $notificationCenter,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        Adapter $config
    ) {
        $this->viewCombination    = $viewCombination;
        $this->notificationCenter = $notificationCenter;
        $this->tokenStorage       = $tokenStorage;
        $this->requestStack       = $requestStack;
        $this->config             = $config;
    }

    /**
     * Send the notification.
     *
     * @param AbstractModelAwareEvent|PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function __invoke(AbstractModelAwareEvent $event): void
    {
        if (!$this->wantToHandle($event)
            || ($this->actionName() !== $event->getEnvironment()->getInputProvider()->getParameter('act'))
            || !($notification = $this->findNotification($event))
        ) {
            return;
        }

        $request = $this->requestStack->getMasterRequest();
        $notification->send(
            $this->generateTokens($event, $notification->flatten_delimiter),
            $request->attributes->get('_locale')
        );
    }

    /**
     * Generate the tokens for the notification message.
     *
     * @param AbstractEnvironmentAwareEvent $event            The event.
     * @param string                        $flattenDelimiter The flatten delimiter.
     *
     * @return array
     */
    private function generateTokens(AbstractEnvironmentAwareEvent $event, string $flattenDelimiter): array
    {
        $request   = $this->requestStack->getMasterRequest();
        $pageModel = $request->attributes->get('pageModel');

        $tokens   = [];
        $tokens[] = ['admin_mail' => ($pageModel->adminEmail ?: $this->config->get('adminEmail'))];
        $tokens[] = $this->generateTokensFromModel($event, $flattenDelimiter);
        $tokens[] = $this->generatePropertyLabelTokens($event);
        $tokens[] = $this->generateOptionalToken($event, $flattenDelimiter);
        $tokens[] = $this->generateMemberTokens($flattenDelimiter);

        return $this->generateDataToken(\array_merge(...$tokens));
    }

    /**
     * Generate tokens from the model.
     *
     * @param AbstractEnvironmentAwareEvent $event            The event.
     * @param string                        $flattenDelimiter The flatten delimiter.
     * @param string                        $prefix           The prefix.
     * @param ModelInterface|Model|null     $model            The model.
     *
     * @return array
     */
    protected function generateTokensFromModel(
        AbstractEnvironmentAwareEvent $event,
        string $flattenDelimiter,
        string $prefix = 'model_',
        ModelInterface $model = null
    ): array {
        if (null === $model) {
            $model = $event->getModel();
        }

        $item       = $model->getItem();
        $values     = $item->parseValue();
        $tokens     = [];
        $properties = $event->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperties();
        foreach ($properties as $property) {
            $attribute = $item->getAttribute($property->getName());
            if ($attribute && isset($values['text'][$property->getName()])) {
                $value = $values['text'][$property->getName()];
            } else {
                $value = $model->getProperty($property->getName());
            }

            if (\is_array($value)) {
                $value = \implode($flattenDelimiter, $value);
            }

            $tokens[$prefix . $property->getName()] = $value;
        }

        $additionals = \array_diff_key($model->getPropertiesAsArray(), $properties);
        foreach ($additionals as $propertyName => $propertyValue) {
            if (\is_array($propertyValue)) {
                $propertyValue = \implode($flattenDelimiter, $propertyValue);
            }

            $tokens[$prefix . $propertyName] = $propertyValue;
        }

        return $tokens;
    }

    /**
     * Generate the member tokens, if the member is logged in.
     *
     * @param string $flattenDelimiter The flatten delimiter.
     *
     * @return array
     */
    private function generateMemberTokens(string $flattenDelimiter): array
    {
        if (!($token = $this->tokenStorage->getToken())
            || !(($user = $token->getUser()) instanceof FrontendUser)
        ) {
            return [];
        }

        $tokens = [];
        foreach ($user->getData() as $key => $value) {
            if (\is_array($value)) {
                $value = \implode($flattenDelimiter, $value);
            }

            $tokens['member_' . $key] = $value;
        }

        return $tokens;
    }

    /**
     * Generate the data token.
     *
     * @param array $tokens The tokens.
     *
     * @return array
     */
    private function generateDataToken(array $tokens): array
    {
        $data = '';
        foreach ($tokens as $key => $value) {
            $data .= $key . ': ' . $value . PHP_EOL;
        }

        $tokens['data'] = $data;

        return $tokens;
    }

    /**
     * Generate tokens from the property.
     *
     * @param AbstractEnvironmentAwareEvent $event The event.
     *
     * @return array
     */
    private function generatePropertyLabelTokens(AbstractEnvironmentAwareEvent $event): array
    {
        $tokens     = [];
        $properties = $event->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperties();
        foreach ($properties as $property) {
            $tokens['property_label_' . $property->getName()] = $property->getLabel() ?? $property->getName();
        }

        return $tokens;
    }

    /**
     * Find the notification.
     *
     * @param AbstractModelAwareEvent $event The event.
     *
     * @return Notification|null
     */
    private function findNotification(AbstractModelAwareEvent $event): ?Notification
    {
        $inputScreen = $this->viewCombination->getScreen($event->getEnvironment()->getDataDefinition()->getName());
        if (!$inputScreen
            || !isset($inputScreen['meta'][$this->metaName()])
            || !($notificationID = $inputScreen['meta'][$this->metaName()])
            || !($notification = $this->notificationCenter->findByPk($notificationID))
        ) {
            return null;
        }

        return $notification;
    }

    /**
     * The action name.
     *
     * @return string
     */
    abstract protected function actionName(): string;

    /**
     * The input screen meta name.
     *
     * @return string
     */
    abstract protected function metaName(): string;

    /**
     * The generate optional token.
     *
     * @param AbstractEnvironmentAwareEvent $event            The event.
     * @param string                        $flattenDelimiter The flatten delimiter.
     *
     * @return array
     */
    abstract protected function generateOptionalToken(
        AbstractEnvironmentAwareEvent $event,
        string $flattenDelimiter
    ): array;
}
