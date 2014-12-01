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
    const LOCALE_PARSER_SERVICE = 'hateoas.request_parser.locale',
        TRANSLATABLE_LISTENER_SERVICE
            = 'stof_doctrine_extensions.listener.translatable',
        TAG_NAME = 'hateoas.request_parser.locale',
        SET_NEGOTIATOR_METHOD = 'setLocaleNegotiator',
        SET_TRANSLATABLE_LISTENER = 'setTranslatableListener';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::LOCALE_PARSER_SERVICE)) continue;

        $definition = $container->getDefinition(self::LOCALE_PARSER_SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall(
                self::SET_NEGOTIATOR_METHOD, [new Reference($id)]
            );
        }

        if ($container->hasDefinition(self::TRANSLATABLE_LISTENER_SERVICE)) {
            $definition->addMethodCall(
                self::SET_TRANSLATABLE_LISTENER,
                [new Reference(self::TRANSLATABLE_LISTENER_SERVICE)]
            );
        }

        return $this;
    }
}
