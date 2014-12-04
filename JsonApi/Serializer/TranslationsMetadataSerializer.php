<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// JSON-API
use GoIntegro\Bundle\HateoasBundle\JsonApi;
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
     * @param JsonApi\Document $document
     * @return array
     */
    public function serialize(JsonApi\Document $document)
    {
        $json = [];

        if ($document->i18n) {
            $docTranslations = $this->findTranslations($document->resources);

            if (!empty($docTranslations)) {

                if ($document->wasCollection) {
                    foreach (
                        $docTranslations as $id => $translations
                    ) {
                        $translations
                            = static::rearrangeTranslations($translations);
                        $json[] = array_merge(compact('id'), $translations);
                    }
                } else {
                    $translations = reset($docTranslations);
                    $json = static::rearrangeTranslations($translations);
                }
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

    /**
     * @param JsonApi\ResourceCollection $resources
     * @return array
     */
    private function findTranslations(JsonApi\ResourceCollection $resources)
    {
        $allTranslations = [];
        $repository = $this->em->getRepository(
            'Gedmo\\Translatable\\Entity\\Translation'
        );

        if (!empty($repository)) { // Do we have Gedmo?
            foreach ($resources as $resource) {
                $translations
                    = $repository->findTranslations($resource->entity);

                if (!empty($translations)) {
                    $allTranslations[(string) $resource->entity->getId()]
                        = $translations;
                }
            }
        }

        return $allTranslations;
    }
}
