<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Config;

// Config.
use Symfony\Component\Config\ConfigCache,
    GoIntegro\Raml\Config\RamlDocCache,
    GoIntegro\Raml\RamlDoc;
// Kernel.
use Symfony\Component\HttpKernel\KernelInterface;

class RamlDocSymfonyCache implements RamlDocCache
{
    const CACHE_SCRIPT_PATH = '/hateoas/resourceEntityMap.php';

    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var ConfigCache
     */
    private $configCache;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        $cacheScriptPath = $this->kernel->getCacheDir()
            . self::CACHE_SCRIPT_PATH;
        $this->configCache = new ConfigCache(
            $cacheScriptPath, $this->kernel->isDebug()
        );
    }

    /**
     * @return boolean
     */
    public function isFresh()
    {
        return $this->configCache->isFresh();
    }

    /**
     * @return RamlDoc $doc
     * @return self
     */
    public function keep(RamlDoc $doc)
    {
        $code = var_export($doc, TRUE);
        $code = "<?php return $code;";
        $this->configCache->write($code);

        return $this;
    }

    /**
     * @return array
     */
    public function read()
    {
        $cacheScriptPath = $this->kernel->getCacheDir()
            . self::CACHE_SCRIPT_PATH;

        return require $cacheScriptPath;
    }
}
