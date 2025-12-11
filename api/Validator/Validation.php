<?php

namespace Webmakerr\Api\Validator;

use Webmakerr\Framework\Response\Response;
use Webmakerr\Framework\Validator\Validator;

abstract class Validation
{
    public static function validate($data, $rules = [])
    {
        $validator = (new Validator())->make($data, empty($rules) ? static::rules() : $rules);

        if($validator->validate()->fails()) {
            $response = new Response();
            return $response->json(['errors' => $validator->errors()], 403);
        }

        return false;
    }
}
