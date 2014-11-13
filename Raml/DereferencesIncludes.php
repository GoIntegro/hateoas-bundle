<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

/**
 * A class that has this trait dereferences includes.
 *
 * I'm tryint to start a new naming convention in which traits are names like
 * traits. What are this classes traits? Well, it's handsome, polite, and it
 * dereferences includes.
 */
trait DereferencesIncludes
{
    /**
     * @param array &$map
     * @param string $fileDir
     * @return array
     */
    protected function dereferenceIncludes(array &$map, $fileDir = '')
    {
        foreach ($map as $key => &$value) {
            if (is_string($value)) {
                if (RamlDoc::isInclude($value)) {
                    $value = $this->dereferenceInclude($value, $fileDir);
                }
            } else {
                throw new \ErrorException(self::ERROR_UNEXPECTED_VALUE);
            }
        }
    }

    /**
     * @param string $value
     * @param string $fileDir
     * @return value
     * @todo Support other file types.
     */
    protected function dereferenceInclude($value, $fileDir = '')
    {
        $filePath = $fileDir . preg_replace('/^!include +/', '/', $value);

        return $this->jsonCoder->decode($filePath, TRUE);
    }
}
