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
class TranslationsParser implements BodyParserInterface
{
    const ERROR_TRANSLATIONS_TYPE = "Translations is expected to be a list of hashes.",
        ERROR_MISSING_ID = "The resource Id is missing for one of the given translations.",
        ERROR_DUPLICATED_TRANSLATION = "Two or more translations were provided twice for the same field and locale in an entity.",
        ERROR_MALFORMED_TRANSLATION = "The translation is missing a locale or a value.";

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
            foreach (
                $body['meta'][$params->primaryType]['translations']
                    as $resourceTranslations
            ) {
                if (empty($resourceTranslations['id'])) {
                    throw new ParseException(self::ERROR_MISSING_ID);
                }

                $id = $resourceTranslations['id'];

                foreach (
                    $resourceTranslations as $field => $fieldTranslations
                ) {
                    if (!is_array($fieldTranslations)) continue;

                    foreach ($fieldTranslations as $translation) {
                        extract($translation); // $locale, $value.

                        if (empty($locale) || empty($value)) {
                            throw new ParseException(
                                self::ERROR_MALFORMED_TRANSLATION
                            );
                        }

                        if (!empty($translations[$id][$locale][$field])) {
                            throw new ParseException(
                                self::ERROR_DUPLICATED_TRANSLATION
                            );
                        }

                        $translations[$id][$locale][$field] = $value;
                    }
                }
            }
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
