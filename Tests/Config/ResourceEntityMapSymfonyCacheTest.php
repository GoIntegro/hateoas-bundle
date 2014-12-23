<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Config;

// Mocks.
use Codeception\Util\Stub;

class ResourceEntityMapSymfonyCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testParsingASimpleRequest()
    {
        // Given...
        $kernel = Stub::makeEmpty(
            'Symfony\\Component\\HttpKernel\\KernelInterface'
        );
        $cache = new ResourceEntityMapSymfonyCache($kernel);
        // When...
        $isFresh = $cache->isFresh();
        // Then...
        $this->assertTrue($isFresh);
    }
}
