<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Architecture');

// Unit tests boot the application so that config()/app() helpers are available,
// but they do not touch the database.
pest()->extend(TestCase::class)
    ->in('Unit');
