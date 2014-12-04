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
        // @todo Make services out of these guys.
        $this->document = $document;

        $json = [];

        // @todo El TopLevelLinksSerializer debería ir primero, pero depende de que el LinkedResourcesSerializer arme el Document.
        $this->addMetadata($json)
            ->addLinkedResources($json)
            ->addPrimaryResources($json)
            ->addTopLevelLinks($json);

        // @todo Arreglo temporal para el orden de las llaves principales.
        return array_reverse($json);
    }

    private function addPrimaryResources(array &$json)
    {
        $metadata = $this->document->resources->getMetadata();
        $fields = isset($this->document->sparseFields[$metadata->type])
            ? $this->document->sparseFields[$metadata->type]
            : [];
        $primaryResources = [];

        foreach ($this->document->resources as $resource) {
            $primaryResources[]
                = $this->serializeResourceObject($resource, $fields);
        }

        $json[$metadata->type] = $this->document->wasCollection
            ? $primaryResources
            : @$primaryResources[0];

        return $this;
    }

    /**
     * Ojo - variables dinámicas.
     * @param array &$json
     * @return self
     */
    protected function addTopLevelLinks(array &$json)
    {
        $name = 'links';

        if (
            0 < count($this->document)
            and $$name = $this->topLevelLinkSerializer->serialize()
        ) {
            $json[$name] = $$name;
        }

        return $this;
    }

    /**
     * Ojo - variables dinámicas.
     * @param array &$json
     * @return self
     */
    protected function addLinkedResources(array &$json)
    {
        $name = 'linked';

        if (
            0 < count($this->document)
            and $$name = $this->linkedResourcesSerializer->serialize()
        ) {
            $json[$name] = $$name;
        }

        return $this;
    }

    protected function addMetadata(array &$json)
    {
        $meta = $this->metadataSerializer->serialize($this->document);

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
