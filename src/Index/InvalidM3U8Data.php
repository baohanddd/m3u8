<?php
namespace Bob\M3U8\Index;

use Bob\M3U8\Filename\Filename;
use Exception;

/**
 * 如果解析出来的m3u8数据有误，则抛出此异常
 * Class InvalidSegmentData
 * @package Bob\M3U8\Index
 */
class InvalidM3U8Data extends Exception
{
    /**
     * @param Filename $filename
     * @param string $content
     */
    public function __construct(Filename $filename, string $content = "")
    {
        $message = "Invalid m3u8 called `{$filename}` with ".substr($content, 0, 512);
        $code = 400;
        parent::__construct($message, $code);
    }
}