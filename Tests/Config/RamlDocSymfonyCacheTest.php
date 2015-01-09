<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Config;

// Mocks.
use Codeception\Util\Stub;
// YAML.
use Symfony\Component\Yaml\Yaml;

class RamlDocSymfonyCacheTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_SCHEMA_RAML = '/../Resources/raml/test-sample.raml';

    public function testCheckingFreshnessOfCache()
    {
        // Given...
        $kernel = Stub::makeEmpty(
            'Symfony\\Component\\HttpKernel\\KernelInterface',
            ['isDebug' => TRUE]
        );
        $cache = new RamlDocSymfonyCache($kernel);
        // When...
        $isFresh = $cache->isFresh();
        // Then...
        $this->assertFalse($isFresh);
    }
}
