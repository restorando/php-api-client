<?php

namespace Restorando\Api;

use Restorando\Api\AbstractApi;
use Restorando\Exception\MissingArgumentException;

class Restaurant extends AbstractApi
{
    public function all()
    {
        return $this->get('restaurants');
    }

    public function show($id)
    {
        return $this->get('restaurants/'.urlencode($id));
    }

}
