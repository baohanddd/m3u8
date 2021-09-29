<?php
namespace App\Test;

use PHPUnit\Framework\TestCase;

class MicroTimeTest extends TestCase
{
    public function testGet()
    {
        $time = gettimeofday();
        $this->assertEquals(10, strlen($time['sec']));
        $this->assertEquals(6, strlen($time['usec']));
    }
}