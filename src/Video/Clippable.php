<?php
namespace Bob\M3U8\Video;

/**
 * 剪辑接口
 * Interface Clippable
 * @package Bob\M3U8\Video
 */
interface Clippable
{
    /**
     * @param float $start
     * @param float $end
     * @return Cuttable
     */
    public function clip(float $start, float $end): Cuttable;
}