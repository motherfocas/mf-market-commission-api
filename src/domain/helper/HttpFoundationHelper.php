<?php

namespace domain\helper;

use Symfony\Component\HttpFoundation\Request;

class HttpFoundationHelper
{
    public function fillRequestFromJson(Request $request)
    {
        $decodedJson = json_decode($request->getContent(), true);

        if($decodedJson != null) {
            $request->request->add($decodedJson);
        }
    }
}
