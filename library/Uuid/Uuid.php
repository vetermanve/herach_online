<?php

namespace Uuid;

use \Ramsey\Uuid\Uuid as UuidRamsey;

class Uuid
{
    public static function v4()
    {
        return UuidRamsey::uuid4();
    }
}