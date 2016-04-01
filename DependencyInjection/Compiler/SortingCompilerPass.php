<?php
/**
 * @copyright 2015 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Sebastián Mensi <sebastian.mensi@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\DependencyInjection\Compiler;

// DI.
use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\Reference;

class SortingCompilerPass implements CompilerPassInterface
{
    const SERVICE_NAME = 'hateoas.repo_helper',
        TAG_NAME = 'hateoas.repo_helper.sorting',
        METHOD_NAME = 'addSorting';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_NAME)) {
            return;
        }

        $definition = $container->getDefinition(self::SERVICE_NAME);
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall(
                self::METHOD_NAME, [new Reference($id)]
            );
        }

        return $this;
    }
}
