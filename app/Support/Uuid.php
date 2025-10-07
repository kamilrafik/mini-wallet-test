<?php

namespace App\Support;

class Uuid
{
    public static function generate(): string
    {
        return \Symfony\Component\Uid\Uuid::v7()->toRfc4122();
    }
}
