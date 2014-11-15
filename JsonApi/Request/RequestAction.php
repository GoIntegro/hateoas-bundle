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
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $target;
}
