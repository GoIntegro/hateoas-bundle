<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Reflexión.
use ReflectionClass,
    ReflectionMethod,
    GoIntegro\Bundle\HateoasBundle\Util\Reflection;
// Inflexión.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;

/**
 * Los datos comunes a todos los recursos de un mismo tipo.
 * @todo Implement the Serializable interface.
 */
class ResourceMetadata implements \Serializable
{
    public $type;
    public $subtype;
    public $resourceClass;
    public $fields;
    public $relationships;

    public function __construct(
        $type,
        $subtype,
        ReflectionClass $resourceClass,
        ResourceFields $fields,
        ResourceRelationships $relationships,
        $pageSize = NULL
    )
    {
        $this->type = $type;
        $this->subtype = $subtype;
        $this->resourceClass = $resourceClass;
        $injectedFields = $this->getInjectedFields();
        $whitelist = $resourceClass->getProperty('fieldWhitelist')->getValue();
        $blacklist = $resourceClass->getProperty('fieldBlacklist')->getValue();
        $this->fields = !empty($whitelist)
            ? $fields->reset($whitelist)
            : $fields->clean($blacklist)
                ->inject($injectedFields);
        $this->relationships = $relationships->clean(
            $resourceClass->getProperty('relationshipBlacklist')->getValue(),
            $resourceClass->getProperty('linkOnlyRelationships')->getValue()
        );
    }

    /**
     * @return array
     */
    protected function getInjectedFields()
    {
        $fields = [];

        foreach ($this->resourceClass->getMethods() as $method) {
            // Este método no califica por no ser público.
            if (Reflection::isMethodInjector($method)) {
                $fields[] = self::fieldFromInjector($method);
            }
        }

        return $fields;
    }

    /**
     * @param ReflectionMethod $method
     * @return string
     * @todo ¿Quizás moverlo al Inflector?
     */
    private static function fieldFromInjector(ReflectionMethod $method)
    {
        $name = substr($method->getShortName(), strlen('inject'));

        return Inflector::hyphenate($name);
    }

    /**
     * @param string $field
     * @return boolean
     */
    public function isField($field)
    {
        return in_array($field, $this->fields->all());
    }

    public function isToOneRelationship($relationship)
    {
        return isset($this->relationships->toOne[$relationship]);
    }

    public function isToManyRelationship($relationship)
    {
        return isset($this->relationships->toMany[$relationship]);
    }

    public function isLinkOnlyRelationship($relationship)
    {
        return isset($this->relationships->linkOnly[$relationship]);
    }

    public function isRelationship($relationship)
    {
        return $this->isToOneRelationship($relationship)
            || $this->isToManyRelationship($relationship)
            || $this->isLinkOnlyRelationship($relationship);
    }

    public function hasRelationships()
    {
        return !empty($this->relationships->toOne)
            || !empty($this->relationships->toMany)
            || !empty($this->relationships->linkOnly);
    }

    /**
     * @see \Serializable::serialize
     */
    public function serialize()
    {
        $export = [
            'type' => $this->type,
            'subtype' => $this->subtype,
            'resourceClass' => $this->resourceClass,
            'fields' => $this->fields,
            'relationships' => $this->relationships
        ];

        return serialize($export);
    }

    /**
     * @see \Serializable::unserialize
     */
    public function unserialize($import)
    {
        $import = unserialize($import);
        $this->type = $import['type'];
        $this->subtype = $import['subtype'];
        $this->resourceClass = $import['resourceClass'];
        $this->fields = $import['fields'];
        $this->relationships = $import['relationships'];
    }
}
