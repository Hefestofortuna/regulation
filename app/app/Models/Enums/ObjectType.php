<?php

namespace App\Models\Enums;

class ObjectType
{
    public const SWTCH = 3207;
    public const RC = 3209;
    public const PWRSPL = 3211;
    public const SGNL = 3213;
    public const CBL = 3215;
    public const CRSS = 3217;
    public const OPCENTR = 3219;
    public const KTSM = 3221;
    public const UKSPS = 3223;

    public static function fromString(string $type): ?int
    {
        return match ($type) {
            'swtch' => self::SWTCH,
            'rc' => self::RC,
            'pwrspl' => self::PWRSPL,
            'sgnl' => self::SGNL,
            'cbl' => self::CBL,
            'crss' => self::CRSS,
            'opcentr' => self::OPCENTR,
            'ktsm' => self::KTSM,
            'uksps' => self::UKSPS,
            default => null,
        };
    }
}
