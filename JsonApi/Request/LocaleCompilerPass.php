<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// DI.
use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\Reference;

class LocaleCompilerPass implements CompilerPassInterface
{
    const SERVICE_NAME = 'hateoas.request_parser.locale',
        TAG_NAME = 'hateoas.request_parser.locale',
        METHOD_NAME = 'setCustomNegotiator';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_NAME)) continue;

        $definition = $container->getDefinition(self::SERVICE_NAME);
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall(
                self::METHOD_NAME, [new Reference($id)]
            );
        }
    }
}
