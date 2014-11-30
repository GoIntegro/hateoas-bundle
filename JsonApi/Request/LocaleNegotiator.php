<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;

class LocaleNegotiator implements LocaleNegotiatorInterface
{
    const ERROR_NO_NEGOTIATOR = "No locale negotiator service has been configured.";

    /**
     * @var LocaleNegotiatorInterface
     */
    private $localeNegotiator;

    /**
     * @param Request $request
     */
    public function negotiate(Request $request)
    {
        if (empty($this->localeNegotiator)) {
            throw new \ErrorException(self::ERROR_NO_NEGOTIATOR);
        }

        return $this->localeNegotiator->negotiate($request);
    }

    /**
     * @param LocaleNegotiatorInterface $negotiator
     */
    public function setLocaleNegotiator(LocaleNegotiatorInterface $negotiator)
    {
        $this->localeNegotiator = $negotiator;
    }
}
