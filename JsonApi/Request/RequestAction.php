<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

class RequestAction
{
    const ACTION_FETCH = 'fetch',
        ACTION_CREATE = 'create',
        ACTION_UPDATE = 'update',
        ACTION_DELETE = 'delete';

    const TYPE_SINGLE = 'single',
        TYPE_MULTIPLE = 'multiple';

    const TARGET_RESOURCE = 'resource',
        TARGET_RELATIONSHIP = 'relationship';

    /**
     * @var string The kind of action; C-F-U-D.
     */
    public $name;
    /**
     * @var string Whether we want an individual or collection representation.
     * @see http://jsonapi.org/format/#document-structure-resource-representations
     */
    public $type;
    /**
     * @var string Whether we're acting upon the resource or its relationships.
     */
    public $target;
}
