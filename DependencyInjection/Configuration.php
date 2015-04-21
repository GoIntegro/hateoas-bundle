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
		->arrayNode('resources')
		  ->prototype('array')
			->children()
				->scalarNode('type')->end()
				->scalarNode('class')->end()
				->arrayNode('defaults')
				    ->children()
					->booleanNode('pagination')->end()
				    ->end()
				->end()
			->end()
		  ->end()
	    ->end()
                ->arrayNode('cache')
                    ->isRequired()
                    ->addDefaultsIfNotSet(TRUE)
                    ->children()
                        ->arrayNode('resource')
                            ->addDefaultsIfNotSet(TRUE)
                            ->children()
                                ->arrayNode('redis')
                                    ->addDefaultsIfNotSet(TRUE)
                                    ->children()
                                        ->arrayNode('parameters')
                                            ->addDefaultsIfNotSet(TRUE)
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
                                        ->arrayNode('options')
                                            ->addDefaultsIfNotSet(TRUE)
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('memcached')
                                    ->addDefaultsIfNotSet(TRUE)
                                    ->children()
                                        ->scalarNode('persistent_id')->end()
                                        ->arrayNode('servers')
                                            ->addDefaultChildrenIfNoneSet(TRUE)
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
