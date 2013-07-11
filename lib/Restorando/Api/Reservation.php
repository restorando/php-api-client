<?php

namespace Restorando\Api;

use Restorando\Api\AbstractApi;
use Restorando\Exception\MissingArgumentException;

class Reservation extends AbstractApi
{
    public function create(array $attributes)
    {
        return $this->post('reservations', $attributes);
    }

}
