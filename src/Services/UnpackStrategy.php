<?php

namespace App\Services;

interface UnpackStrategy
{
    public function unpack($source, $destination, $extension);
}

