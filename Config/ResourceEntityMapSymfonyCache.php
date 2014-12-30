<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Config;

// Config.
use Symfony\Component\Config\ConfigCache,
    GoIntegro\Hateoas\Config\ResourceEntityMapCache;
// Kernel.
use Symfony\Component\HttpKernel\KernelInterface;

class ResourceEntityMapSymfonyCache implements ResourceEntityMapCache
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
     * @return array $map
     * @return self
     */
    public function keep(array $map)
    {
        $code = var_export($map, TRUE);
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
