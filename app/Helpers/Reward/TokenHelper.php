<?php

namespace App\Helper\Reward;

use App\Model\Reward\Token;
use App\Model\Reward\TokenGenerator;

class TokenHelper
{
    public static function add($source)
    {
        $tokenGenerator = TokenGenerator::where('source', $source)->first();

        $token = new Token([
            'user_id' => auth()->user()->id,
            'source' => $tokenGenerator->source,
            'amount' => $tokenGenerator->amount,
        ]);

        $token->save();

        return $token;
    }
}
