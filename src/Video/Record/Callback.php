<?php
namespace Bob\M3U8\Video\Record;

/**
 * 回放生成事件
 * Class Callback
 * @package Bob\M3U8\Video\Record
 */
class Callback
{
    /**
     * 开始记录回放
     */
    const EVENT_RECORD_START    = 1;
    /**
     * 停止记录回放
     */
    const EVENT_RECORD_PAUSE    = 2;
    /**
     * 生成回放文件
     */
    const EVENT_RECORD_GENERATE = 3;

    /**
     * @param string $event
     * @return int
     */
    public static function getEvent(string $event): int
    {
        switch (strtolower($event)) {
            case 'record_started':
                $evt = self::EVENT_RECORD_START;
                break;
            case 'record_paused':
                $evt = self::EVENT_RECORD_PAUSE;
                break;
            default:
                $evt = self::EVENT_RECORD_GENERATE;
        }
        return $evt;
    }
}