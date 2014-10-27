<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

/**
 * La metadata de una relación.
 */
class ResourceRelationship
{
    const TO_ONE = 'toOne', TO_MANY = 'toMany', LINK_ONLY = 'linkOnly';

    public $class;
    public $type;
    public $subtype;
    public $kind;
    public $name;
    public $mappingField;

    /**
     * @param string $class
     * @param string $type
     * @param string $subtype
     * @param string $kind
     * @param string $name
     * @param string $mappingField
     */
    public function __construct(
        $class, $type, $subtype, $kind, $name, $mappingField
    )
    {
        $this->class = $class;
        $this->type = $type;
        $this->subtype = $subtype;
        $this->kind = $kind;
        $this->name = $name;
        $this->mappingField = $mappingField;
    }
}
