<?php
namespace Bob\M3U8;

use Bob\M3U8\Component\UUID;
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
}