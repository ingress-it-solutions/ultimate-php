<?php


namespace Ultimate\Models\Partials;


use Ultimate\Models\Arrayable;

class Http extends Arrayable
{
    /**
     * Http constructor.
     */
    public function __construct()
    {

        $this->request = new Request();
        $this->url = new Url();
    }
}
