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
        $micro->usec = str_pad($now['usec'], 6, 0, STR_PAD_RIGHT);
        return $micro;
    }
    
    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
    
    /**
     * @return int
     */
    public function getUsec(): int
    {
        return $this->usec;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->timestamp}{$this->usec}";
    }
}