<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Contao\FrontendEditing;

use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\FrontendEditor;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\FrontendIntegration\MetaModelHybrid;

/**
 * This class is the base for the frontend integrations.
 */
abstract class FrontendEditHybrid extends MetaModelHybrid
{
    /**
     * The template to use.
     *
     * @var string
     */
    protected $strTemplate = 'dc-general-frontend';

    /**
     * The name to display in the wildcard.
     *
     * @var string
     */
    protected $wildCardName = '### METAMODELS FRONTEND EDITING ###';

    /**
     * The table name to generate the frontend editing for.
     *
     * @var string
     */
    protected $table;

    /**
     * Compile the content element.
     *
     * @return void
     */
    protected function compile()
    {
        $container = $this->getServiceContainer();
        $metaModel = $container->getFactory()->translateIdToMetaModelName($this->metamodel);
        $editor    = new FrontendEditor($container->getEventDispatcher(), $this->getTranslator());

        $this->Template->editor = $editor->editFor($metaModel, 'create');
    }

    /**
     * Get the translator from the service container.
     *
     * @return TranslatorInterface
     *
     * @throws \RuntimeException When the DIC or translator have not been correctly initialized.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getTranslator()
    {
        if (!($container = $GLOBALS['container']) instanceof \Pimple) {
            throw new \RuntimeException('The dependency container has not been initialized correctly.');
        }

        $translator = $container['translator'];

        if (!$translator instanceof TranslatorInterface) {
            throw new \RuntimeException('The dependency container has not been initialized correctly.');
        }

        return $translator;
    }
}
