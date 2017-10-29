<?php

namespace domain\response;

use Symfony\Component\HttpFoundation\Response;

class JsonResponse extends Response
{
    public function __construct($content = '', $status = 200)
    {
        parent::__construct($content, $status, ['Content-Type' => 'application/json']);
    }
}
