<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;

/**
 * @see http://jsonapi.org/format/#crud-updating-relationships
 */
class ResourceLinksHydrant
{
    const LINKS = 'links',
        ERROR_LINKS_TYPE = "Links should be an object.", // JSON object.
        ERROR_LINKS_CONTENT = "The key \"%s\" does not correspond to a relationship.",
        ERROR_LINKS_CONTENT_TYPE = "The relationship \"%s\" has an invalid value.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var MetadataMinerInterface Couldn't resist my OCD.
     */
    private $mm;

    /**
     * @param EntityManagerInterface $em
     * @param MetadataMinerInterface $mm
     */
    public function __construct(
        EntityManagerInterface $em,
        MetadataMinerInterface $mm
    )
    {
        $this->em = $em;
        $this->mm = $mm;
    }

    /**
     * @param Parser $params
     * @param array &$resourceObject
     * @throws ParseException
     */
    public function hydrate(Params $params, array &$resourceObject)
    {
        if (empty($resourceObject[self::LINKS])) {
            return;
        } elseif (!is_array($resourceObject[self::LINKS])) {
            throw new ParseException(self::ERROR_LINKS_TYPE);
        }

        $metadata = $this->mm->mine($params->primaryClass);

        foreach ($resourceObject[self::LINKS] as $relationship => &$ids) {
            if (is_array($ids)) {
                if (!$metadata->isToManyRelationship($relationship)) {
                    $message = sprintf(
                        self::ERROR_LINKS_CONTENT, $relationship
                    );
                    throw new ParseException($message);
                }

                $class
                    = $metadata->relationships->toMany[$relationship]->class;
                $ids = $this->em->getRepository($class)->findById($ids);
            } elseif (is_string($ids)) {
                if (!$metadata->isToOneRelationship($relationship)) {
                    $message = sprintf(
                        self::ERROR_LINKS_CONTENT, $relationship
                    );
                    throw new ParseException($message);
                }

                $class
                    = $metadata->relationships->toOne[$relationship]->class;
                $ids = $this->em->getRepository($class)->findOneById($ids);
            } elseif (!is_null($ids)) {
                $message = sprintf(
                    self::ERROR_LINKS_CONTENT_TYPE, $relationship
                );
                throw new ParseException($message);
            }
        }
    }
}
