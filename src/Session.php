<?php
namespace Bob\M3U8;

use Bob\M3U8\Component\UUID;
use Closure;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Session
{
    /**
     * @param int $level
     * @return Logger
     */
    public static function getLog(int $level = Logger::DEBUG): Logger
    {
        static $log;
        if ($log == null) {
            $log = new Logger('M3U8');
            $requestId = UUID::guidv4();
            $log->pushProcessor(function ($record) use ($requestId) {
                $record['request_id'] = $requestId;
                return $record;
            });
            $log->pushHandler(new StreamHandler('php://stdout', $level));
        }
        return $log;
    }
    
    /**
     * @return Closure
     */
    public static function getM3U8Uploader(): Closure
    {
        return function(string $uploadName, string $content): string {
            Session::getLog()->debug("The m3u8 file is uploaded successful...");
            return "https://play.futurelink.live/{$uploadName}";
        };
    }
    
    /**
     * @return Closure
     */
    public static function getBlockUploader(): Closure
    {
        return function(string $uploadName, string $tempName): string {
            Session::getLog()->debug("The block file called `{$tempName}` is uploaded successful and named `{$uploadName}`...");
            return "https://play.futurelink.live/{$uploadName}";
        };
    }
    
    /**
     * @param string $host
     * @return bool
     */
    public static function isClippableDomain(string $host): bool
    {
        return in_array($host, [
            'video3.futurelink.live'
        ]);
    }
    
    public static function getFFMPEG(): string
    {
        return "/data/bin/ffmpeg";
    }
}