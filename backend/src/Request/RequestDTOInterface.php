<?php

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

interface RequestDTOInterface
{
    public function __construct(Request $request);
}
