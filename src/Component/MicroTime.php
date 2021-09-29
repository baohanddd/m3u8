<?php
namespace Bob\M3U8\Component;

/**
 * Generator of Micro time
 */
class MicroTime
{
    /**
     * @var int
     */
    protected int $timestamp;
    
    /**
     * @var int
     */
    protected int $usec;
    
    /**
     * @return MicroTime
     */
    public static function create(): MicroTime
    {
        $now = gettimeofday();
        $micro = new MicroTime();
        $micro->timestamp = $now['sec'];
        $micro->usec = $now['usec'];
        return $micro;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->timestamp}{$this->usec}";
    }
}