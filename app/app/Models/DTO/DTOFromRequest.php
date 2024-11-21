<?php

namespace App\Models\DTO;

use Illuminate\Http\Request;

interface DTOFromRequest
{
    public static function fromRequest(Request $request): self;
}
