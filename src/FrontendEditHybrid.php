<?php

/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2016-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage ContaoFrontendEditing
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2016-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle;

use Contao\ContentModel;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\FormModel;
use Contao\ModuleModel;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\FrontendEditor;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use MetaModels\FrontendIntegration\MetaModelHybrid;
use MetaModels\IFactory;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
     * @var IFactory
     */
    private $factory;

    /**
     * @var FrontendEditor
     */
    private $editor;

    /**
     * FrontendEditHybrid constructor.
     *
     * @param ContentModel|ModuleModel|FormModel $element
     * @param string                             $column
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function __construct($element, $column = 'main')
    {
        parent::__construct($element, $column);

        $this->factory = System::getContainer()->get('metamodels.factory');
        $this->editor  = System::getContainer()->get('cca.dc-general.contao_frontend.editor');
    }

    /**
     * Compile the content element.
     *
     * @return void
     */
    protected function compile()
    {
        $metaModel = $this->factory->translateIdToMetaModelName($this->metamodel);

        try {
            $this->Template->editor = $this->editor->editFor($metaModel, 'create');
        } catch (DcGeneralRuntimeException $e) {
            throw new AccessDeniedException($e->getMessage());
        }
    }
}
