<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Serializers.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class DocumentSerializer implements Serializer\DocumentSerializerInterface
{
    const ERROR_EMPTY_DOCUMENT = "El documento debe contener al menos un recurso de entidad.";

    private $topLevelLinkSerializer;
    private $linkedResourcesSerializer;
    private $metadataSerializer;
    private $document;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param Serializer\TopLevelLinksSerializer $topLevelLinkSerializer
     * @param Serializer\LinkedResourcesSerializer $linkedResourcesSerializer
     * @param Serializer\MetadataSerializer $metadataSerializer
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        Serializer\TopLevelLinksSerializer $topLevelLinkSerializer,
        Serializer\LinkedResourcesSerializer $linkedResourcesSerializer,
        Serializer\MetadataSerializer $metadataSerializer,
        SecurityContextInterface $securityContext
    )
    {
        $this->topLevelLinkSerializer = $topLevelLinkSerializer;
        $this->linkedResourcesSerializer = $linkedResourcesSerializer;
        $this->metadataSerializer = $metadataSerializer;
        $this->securityContext = $securityContext;
    }

    /**
     * @param Document $document
     * @return array
     */
    public function serialize(Document $document)
    {
        $json = [];

        // @todo El TopLevelLinksSerializer debería ir primero, pero depende de que el LinkedResourcesSerializer arme el Document.
        $this->addMetadata($document, $json)
            ->addLinkedResources($document, $json)
            ->addPrimaryResources($document, $json)
            ->addTopLevelLinks($document, $json);

        // @todo Arreglo temporal para el orden de las llaves principales.
        return array_reverse($json);
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    private function addPrimaryResources(Document $document, array &$json)
    {
        $metadata = $document->resources->getMetadata();
        $fields = isset($document->sparseFields[$metadata->type])
            ? $document->sparseFields[$metadata->type]
            : [];
        $primaryResources = [];

        foreach ($document->resources as $resource) {
            $primaryResources[]
                = $this->serializeResourceObject($resource, $fields);
        }

        $json[$metadata->type] = $document->wasCollection
            ? $primaryResources
            : @$primaryResources[0];

        return $this;
    }

    /**
     * Ojo - variables dinámicas.
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addTopLevelLinks(Document $document, array &$json)
    {
        $name = 'links';

        if (
            0 < count($document)
            and $$name = $this->topLevelLinkSerializer->serialize($document)
        ) {
            $json[$name] = $$name;
        }

        return $this;
    }

    /**
     * Ojo - variables dinámicas.
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addLinkedResources(Document $document, array &$json)
    {
        $name = 'linked';

        if (
            0 < count($document)
            and $$name = $this->linkedResourcesSerializer->serialize($document)
        ) {
            $json[$name] = $$name;
        }

        return $this;
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addMetadata(Document $document, array &$json)
    {
        $meta = $this->metadataSerializer->serialize($document);

        if ($meta) $json['meta'] = $meta;

        return $this;
    }

    /**
     * @param DocumentResource $resource
     * @return array
     */
    protected function serializeResourceObject(
        DocumentResource $resource, array $fields = []
    )
    {
        $serializer = new Serializer\ResourceObjectSerializer(
            $resource, $this->securityContext, $fields
        );

        return $serializer->serialize();
    }
}
