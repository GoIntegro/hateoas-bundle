<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\Reference;

class FilterCompilerPass implements CompilerPassInterface
{
    const SERVICE_NAME = 'hateoas.repo_helper',
        TAG_NAME = 'hateoas.repo_helper.filter',
        ENTITY_CLASS = 'entity_class',
        METHOD_NAME = 'addFilter';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_NAME)) continue;

        $definition = $container->getDefinition(self::SERVICE_NAME);
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    self::METHOD_NAME,
                    [new Reference($id), $attributes[self::ENTITY_CLASS]]
                );
            }
        }
    }
}
