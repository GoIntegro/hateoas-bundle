<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\Reference;

class EntityCompilerPass implements CompilerPassInterface
{
    const SERVICE_PREFIX = 'hateoas.entity.',
        RESOURCE_TYPE = 'resource_type';

    /**
     * @var array
     */
    private static $services = ['builder', 'mutator', 'deleter'];

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        foreach (self::$services as $service) {
            $name = $tag = self::SERVICE_PREFIX . $service;

            if (!$container->hasDefinition($name)) continue;

            $definition = $container->getDefinition($name);
            $taggedServices = $container->findTaggedServiceIds($tag);

            foreach ($taggedServices as $id => $tagAttributes) {
                foreach ($tagAttributes as $attributes) {
                    $definition->addMethodCall(
                        'add' . ucfirst($name),
                        [new Reference($id), $attributes[self::RESOURCE_TYPE]]
                    );
                }
            }
        }
    }
}
