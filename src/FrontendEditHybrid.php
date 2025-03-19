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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle;

use Contao\ContentModel;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\FormModel;
use Contao\ModuleModel;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\FrontendEditor;
use ContaoCommunityAlliance\DcGeneral\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotDeletableException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotEditableException;
use MetaModels\FrontendIntegration\MetaModelHybrid;
use MetaModels\IFactory;

/**
 * This class is the base for the frontend integrations.
 *
 * @psalm-import-type TDatabaseResult from \MetaModels\FrontendIntegration\MetaModelHybrid
 *
 * @psalm-suppress DeprecatedClass
 * @psalm-suppress PropertyNotSetInConstructor
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
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The frontend editor.
     *
     * @var FrontendEditor
     */
    private FrontendEditor $editor;

    /**
     * FrontendEditHybrid constructor.
     *
     * @param TDatabaseResult $element The element model, i.e., the module or content element.
     * @param string          $column  The column.
     */
    public function __construct($element, $column = 'main')
    {
        /** @psalm-suppress DeprecatedClass */
        parent::__construct($element, $column);

        /** @psalm-suppress PropertyTypeCoercion */
        $this->factory = System::getContainer()->get('metamodels.factory');
        /** @psalm-suppress PropertyTypeCoercion */
        $this->editor  = System::getContainer()->get('cca.dc-general.contao_frontend.editor');
    }

    /**
     * Generate the content element/module.
     *
     * @return string
     */
    public function generate(): string
    {
        /** @psalm-suppress UndefinedThisPropertyFetch */
        if ($this->customTpl) {
            /** @psalm-suppress UndefinedThisPropertyFetch */
            $this->strTemplate = $this->customTpl;
        }

        /** @psalm-suppress DeprecatedClass */
        return parent::generate();
    }

    /**
     * Compile the content element/module.
     *
     * @return void
     *
     * @throws AccessDeniedException In case the data container is not allowed to edit.
     */
    protected function compile(): void
    {
        /** @psalm-suppress UndefinedThisPropertyFetch */
        $metaModel = $this->factory->translateIdToMetaModelName($this->metamodel);

        try {
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $this->Template->editor = $this->editor->editFor($metaModel, 'create');
        } catch (NotEditableException $exception) {
            throw new AccessDeniedException($exception->getMessage());
        } catch (NotCreatableException $exception) {
            throw new AccessDeniedException($exception->getMessage());
        } catch (NotDeletableException $exception) {
            throw new AccessDeniedException($exception->getMessage());
        }
    }
}
