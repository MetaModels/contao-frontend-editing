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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ContaoFrontendEditingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;

/**
 * This is the Bundle extension.
 */
class MetaModelsContaoFrontendEditingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('listeners.yml');
        $loader->load('services.yml');

        $bundles = $container->getParameter('kernel.bundles');
        assert(\is_array($bundles));

        $is17 = \array_key_exists('notification_center', $bundles);
        $is20 = \class_exists(Terminal42NotificationCenterBundle::class, true);

        // NC 1.7
        if ($is17) {
            $loader->load('notification/frontend_listeners.yml');
        }
        // NC 1.7 and 2.0.
        if ($is17 || $is20) {
            $loader->load('notification/backend_listeners.yml');
        }
        // NC 2.0.
        if ($is20) {
            $loader->load('notification/types_listeners.yml');
            $loader->load('notification/frontend_nc_listeners.yml');
        }
    }
}
