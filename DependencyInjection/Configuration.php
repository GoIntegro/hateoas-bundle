<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('go_integro_hateoas');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode('json_api')
                    ->children()
                        ->arrayNode('magic_services')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('resource_type')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('entity_class')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('raml_doc')->end()
                                ->end()
                            ->end()
                        ->end()
                        // @todo Use it or lose it.
                        // ->arrayNode('extension')
                        //     ->prototype('array')
                        //         ->children()
                        //             ->scalarNode('serializer_type')
                        //                 ->isRequired()
                        //                 ->cannotBeEmpty()
                        //             ->end()
                        //             ->scalarNode('serializer_class')
                        //                 ->isRequired()
                        //                 ->cannotBeEmpty()
                        //             ->end()
                        //         ->end()
                        //     ->end()
                        // ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->children()
                        ->arrayNode('resource')
                            ->children()
                                ->arrayNode('redis')
                                    ->children()
                                        ->arrayNode('parameters')
                                            ->children()
                                                ->scalarNode('scheme')
                                                    ->defaultValue('tcp')
                                                ->end()
                                                ->scalarNode('host')
                                                    ->defaultValue('127.0.0.1')
                                                ->end()
                                                ->scalarNode('port')
                                                    ->defaultValue('6379')
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('options')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('memcached')
                                    ->children()
                                        ->arrayNode('options')->end()
                                        ->arrayNode('servers')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('host')
                                                        ->defaultValue('127.0.0.1')
                                                    ->end()
                                                    ->scalarNode('port')
                                                        ->defaultValue('11211')
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
