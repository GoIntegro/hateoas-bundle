<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;
// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;
// ORM.
use Doctrine\ORM\EntityManagerInterface;

/**
 * @see http://jsonapi.org/format/#crud-updating
 */
class RelateBodyParser
{
    const ERROR_MISSING_ID = "A data set provided is missing the Id.",
        ERROR_DUPLICATED_ID = "The Id \"%s\" was sent twice.",
        ERROR_EMPTY_BODY = "The resource data was not found on the body.",
        ERROR_INVALID_RELATIONSHIP = "The relationship is not valid.",
        ERROR_INVALID_IDS = "The relationship Ids were not provided in the correct format.";

    /**
     * @var JsonCoder
     */
    protected $jsonCoder;
    /**
     * @var MetadataMinerInterface
     */
    protected $mm;
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @param JsonCoder $jsonCoder
     * @param MetadataMinerInterface $mm
     * @param EntityManagerInterface $em
     */
    public function __construct(
        JsonCoder $jsonCoder,
        MetadataMinerInterface $mm,
        EntityManagerInterface $em
    )
    {
        $this->jsonCoder = $jsonCoder;
        $this->mm = $mm;
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        $rawBody = $request->getContent();
        $data = $this->jsonCoder->decode($rawBody);

        if (!is_array($data) || !isset($data[$params->primaryType])) {
            throw new ParseException(self::ERROR_EMPTY_BODY);
        }

        $ids = $data[$params->primaryType];
        $metadata = $this->mm->mine($params->primaryClass);
        $relation = NULL;

        if ($metadata->isToOneRelationship($params->relationship)) {
            $relation
                = $metadata->relationships->toOne[$params->relationship];

            if (!is_string($ids) && !is_null($ids)) {
                throw new ParseException(self::ERROR_INVALID_IDS);
            }
        } elseif ($metadata->isToManyRelationship($params->relationship)) {
            $relation
                = $metadata->relationships->toMany[$params->relationship];

            if (!is_array($ids)) {
                throw new ParseException(self::ERROR_INVALID_IDS);
            }
        } else {
            throw new ParseException(self::ERROR_INVALID_RELATIONSHIP);
        }

        return $this->em->getRepository($relation->class)->findById($ids);
    }
}
