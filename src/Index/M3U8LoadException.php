<?php
namespace Bob\M3U8\Index;

use Bob\M3U8\Video\Filename;
use Exception;

/**
 * Can not load M3U8 file from the specified address
 */
class M3U8LoadException extends Exception
{
    /**
     * @param Filename $filename
     */
    public function __construct(Filename $filename)
    {
        $message = "Can not load m3u8: {$filename}";
        $code = 400;
        parent::__construct($message, $code);
    }
}