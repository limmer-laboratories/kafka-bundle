<?php

namespace LimLabs\KafkaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use function dirname;

class KafkaBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}