<?php
namespace App\Test;

use Bob\M3U8\Session;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testLog()
    {
        $log1 = Session::getLog();
        $log2 = Session::getLog();
        $this->assertSame($log1, $log2);
        $this->assertInstanceOf(Logger::class, $log1);
    }
}