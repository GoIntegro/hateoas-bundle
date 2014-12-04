<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// JSON-API
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;
// ORM.
use Doctrine\ORM\EntityManagerInterface;

/**
 * @todo Move a un sub-namespace "JsonApi\Extension".
 */
class TranslationsMetadataSerializer implements DocumentSerializerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Document $document
     * @return array
     */
    public function serialize(Document $document)
    {
        $json = [];

        if (!empty($this->document->translations)) {
            if ($this->document->wasCollection) {
                foreach (
                    $this->document->translations as $id => $translations
                ) {
                    $translations
                        = static::rearrangeTranslations($translations);
                    $json[] = array_merge(compact('id'), $translations);
                }
            } else {
                $translations = reset($this->document->translations);
                $json = static::rearrangeTranslations($translations);
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
