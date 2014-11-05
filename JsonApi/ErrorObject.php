<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// UUID
use Rhumsaa\Uuid\Uuid;

/**
 * @see http://jsonapi.org/format/#errors
 */
class ErrorObject
{
    /**
     * @var string A unique identifier for this particular occurrence of the problem.
     */
    public $id;
    /**
     * @var string A URI that MAY yield further details about this particular occurrence of the problem.
     */
    public $href;
    /**
     * @var string The HTTP status code applicable to this problem, expressed as a string value.
     */
    public $status;
    /**
     * @var string An application-specific error code, expressed as a string value.
     */
    public $code;
    /**
     * @var string A short, human-readable summary of the problem. It SHOULD NOT change from occurrence to occurrence of the problem, except for purposes of localization.
     */
    public $title;
    /**
     * @var string A human-readable explanation specific to this occurrence of the problem.
     */
    public $detail;
    /**
     * @var array Associated resources which can be dereferenced from the request document.
     */
    public $links;
    /**
     * @var string The relative path to the relevant attribute within the associated resource(s). Only appropriate for problems that apply to a single resource or type of resource.
     */
    public $path;

    /**
     * Builds an error object model assinging it a UUID.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }
}
