<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// JSON-API
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;

/**
 * @todo Move a un sub-namespace "JsonApi\Extension".
 */
class TranslationsMetadataSerializer implements SerializerInterface
{
    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function serialize()
    {
        $json = [];

        if (!empty($this->document->translations)) {
            foreach ($this->document->translations as $id => $translations) {
                $translations = static::rearrangeTranslations($translations);
                $json[] = array_merge(compact('id'), $translations);
            }
        }

        return $json;
    }

    /**
     * @param array $translations
     * @return array
     */
    private static function rearrangeTranslations(array $byLocale)
    {
        $byField = [];

        foreach ($byLocale as $locale => $fields) {
            foreach ($fields as $field => $value) {
                $byField[$field][] = compact(['locale', 'value']);
            }
        }

        return $byField;
    }
}
