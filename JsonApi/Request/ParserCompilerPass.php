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
    const REQUEST_PARSER_SERVICE = 'hateoas.request_parser',
        TRANSLATABLE_LISTENER_SERVICE
            = 'stof_doctrine_extensions.listener.translatable',
        SET_TRANSLATABLE_LISTENER = 'setTranslatableListener';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition(self::REQUEST_PARSER_SERVICE)
            || !$container->hasDefinition(self::TRANSLATABLE_LISTENER_SERVICE)
        ) {
            continue;
        }

        $definition = $container->getDefinition(self::REQUEST_PARSER_SERVICE);
        $definition->addMethodCall(
            self::SET_TRANSLATABLE_LISTENER,
            [new Reference(self::TRANSLATABLE_LISTENER_SERVICE)]
        );

        return $this;
    }
}
