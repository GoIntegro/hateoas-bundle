<?php

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

interface Factory
{
    /**
     * Crea y configura la instancia.
     * @return mixed
     */
    public function create();
}
