<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request,
    GoIntegro\Bundle\HateoasBundle\Http\Url;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util;

/**
 * @see https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/translatable.md
 */
class TranslationsParser
{
    const ERROR_TRANSLATIONS_TYPE = "Translations is expected to be a hash of lists.";

    /**
     * @var Util\JsonCoder
     */
    private $jsonCoder;

    /**
     * @param Util\JsonCoder $jsonCoder
     */
    public function __construct(Util\JsonCoder $jsonCoder)
    {
        $this->jsonCoder = $jsonCoder;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @param array $body
     * @return array
     * @todo Check if fields are translatable.
     */
    public function parse(Request $request, Params $params, array $body)
    {
        $translations = [];

        if (
            empty($params->primaryType)
            || empty($body['meta'][$params->primaryType]['translations'])
        ) {
            return $translations;
        } else {
            $translations
                = $body['meta'][$params->primaryType]['translations'];
        }

        if (
            !is_array($translations)
            || !Util\ArrayHelper::isAssociative($translations)
        ) {
            throw new ParseException(self::ERROR_TRANSLATIONS_TYPE);
        }

        return $translations;
    }
}
