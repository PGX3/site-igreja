<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Testes não dependem do build do front (public/build), nem local nem no CI.
        $this->withoutVite();
    }
}
