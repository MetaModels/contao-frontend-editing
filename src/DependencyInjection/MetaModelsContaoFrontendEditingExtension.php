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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\DependencyInjection;

use Symfony\Contracts\Config\FileLocator;
use Symfony\Contracts\DependencyInjection\ContainerBuilder;
use Symfony\Contracts\DependencyInjection\Extension\Extension;
use Symfony\Contracts\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the Bundle extension.
 */
class MetaModelsContaoFrontendEditingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('listeners.yml');

        if (\array_key_exists('notification_center', $container->getParameter('kernel.bundles'))) {
            $loader->load('notification/backend_listeners.yml');
            $loader->load('notification/frontend_listeners.yml');
        }
    }
}
