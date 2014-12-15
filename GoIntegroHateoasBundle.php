<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle;

// Symfony 2.
use Symfony\Component\HttpKernel\Bundle\Bundle,
    Symfony\Component\DependencyInjection\ContainerBuilder;
// HATEOAS.
use GoIntegro\Bundle\HateoasBundle\DependencyInjection\Compiler;

class GoIntegroHateoasBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new Compiler\EntityCompilerPass)
            ->addCompilerPass(new Compiler\ParserCompilerPass)
            ->addCompilerPass(new Compiler\FilterCompilerPass)
            ->addCompilerPass(new Compiler\LocaleCompilerPass);
    }
}
