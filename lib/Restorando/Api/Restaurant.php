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

    public function fetch($id)
    {
        return $this->get('restaurants/'.urlencode($id));
    }

    public function availability($id, $date, $diners)
    {
        return $this->get('restaurants/'.urlencode($id) . "/".urlencode($date), array("diners" => $diners));
    }

}
