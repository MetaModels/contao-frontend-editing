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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\EventListener\DcGeneral\ActionEvent;

use Contao\CoreBundle\Exception\RedirectResponseException;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\View\EditMask;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use MetaModels\DcGeneral\Data\Driver;
use MetaModels\IFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class handles the "create variant" actions in the frontend.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateVariantHandler
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * CreateVariantHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     * @param IFactory                 $factory           The MetaModels factory.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, IFactory $factory)
    {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->factory           = $factory;
    }

    /**
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     *
     * @throws RedirectResponseException         To redirect to the edit mask with cloned model.
     * @throws DcGeneralRuntimeException         When the DataContainer is not creatable.
     */
    public function handleEvent(ActionEvent $event): void
    {
        if (!$this->scopeDeterminator->currentScopeIsFrontend()) {
            return;
        }

        $environment = $event->getEnvironment();
        $action      = $event->getAction();

        // Only handle if we do not have a manual sorting or we know where to insert.
        // Manual sorting is handled by clipboard.
        if ('createvariant' !== $action->getName()) {
            return;
        }

        // Only run when no response given yet.
        if (null !== $event->getResponse()) {
            return;
        }

        $response = $this->process($environment);
        if (false !== $response) {
            $event->setResponse($response);
        }
    }

    /**
     * Handle the action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|false
     *
     * @throws NotCreatableException     When the DataContainer is not creatable.
     * @throws DcGeneralRuntimeException When the model to create a variant from was not found.
     */
    public function process(EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);
        $basicDefinition = $definition->getBasicDefinition();
        $dataProvider    = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        if (!$basicDefinition->isCreatable()) {
            throw new NotCreatableException('DataContainer ' . $definition->getName() . ' is not creatable');
        }
        // We only support flat tables, sorry.
        if (BasicDefinitionInterface::MODE_FLAT !== $basicDefinition->getMode()) {
            return false;
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $modelId = ModelId::fromSerialized($inputProvider->getParameter('source'));

        /** @var Driver $dataProvider */
        $model = $dataProvider->createVariant($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        if (null === $model) {
            throw new DcGeneralRuntimeException(
                \sprintf(
                    'Could not find model with id %s for creating a variant.',
                    $modelId->getId()
                )
            );
        }

        $metaModel = $this->factory->getMetaModel($model->getProviderName());
        if (null === $metaModel || false === $metaModel->hasVariants()) {
            return false;
        }

        $preFunction = static function (EnvironmentInterface $environment, ModelInterface $model): void {
            $createModelEvent = new PreCreateModelEvent($environment, $model);
            $dispatcher       = $environment->getEventDispatcher();

            assert($dispatcher instanceof EventDispatcherInterface);
            $dispatcher->dispatch($createModelEvent, $createModelEvent::NAME);
        };

        $postFunction = static function (EnvironmentInterface $environment, ModelInterface $model): void {
            $createModelEvent = new PostCreateModelEvent($environment, $model);
            $dispatcher       = $environment->getEventDispatcher();

            assert($dispatcher instanceof EventDispatcherInterface);
            $dispatcher->dispatch($createModelEvent, $createModelEvent::NAME);
        };

        $newModel = clone $model;
        $editMask = new EditMask($environment, $newModel, $model, $preFunction, $postFunction);

        return $editMask->execute();
    }
}
