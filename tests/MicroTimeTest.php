<?php
namespace App\Test;

use Bob\M3U8\Component\MicroTime;
use PHPUnit\Framework\TestCase;

class MicroTimeTest extends TestCase
{
    public function testGet()
    {
        for($i = 0; $i < 20; $i++) {
            $time = MicroTime::create();
            $this->assertEquals(10, strlen($time->getTimestamp()), $time->getTimestamp());
            $this->assertEquals(6, strlen($time->getUsec()), $time->getUsec());
            usleep(rand(0, 10));
        }
    }
}