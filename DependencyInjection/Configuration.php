<?php

namespace NTI\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nti_notification');

        $rootNode
            ->children()
                ->scalarNode('user_destination_get_method')->end()
            ->end()
            ->children()
                ->arrayNode('user_authentication_roles')->end()
            ->end();

        return $treeBuilder;
    }
}
