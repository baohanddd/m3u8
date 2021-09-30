<?php
namespace Bob\M3U8\Index;

use Bob\M3U8\Session;
use Bob\M3U8\Block\Block;

/**
 * dump m3u8 content out
 * Class Dumper
 * @package Bob\M3U8\Index
 */
class Dumper
{
    /**
     * dump m3u8 content as string
     * @param Timeline $timeline
     * @return string
     */
    public function dump(Timeline $timeline): string
    {
        $log = Session::getLog();
        $text  = $this->getStartHeader();
        $text .= $this->getVersion();
        $text .= $this->getMediaSequence();
        $text .= $this->getTargetDuration($timeline->getMaxLength());    // max block length
        foreach ($timeline->getBlocks() as $block) {
            $discontinuity = $block->getDiscontinuity();
            $log->debug($block->getSaveFilename()->getBasename(), ['discontinuity' => $discontinuity]);
            if ($discontinuity == Block::DISCONTINUITY_PREV || $discontinuity == Block::DISCONTINUITY_BOTH) {
                $text .= $this->getDiscontinuity();
            }
            $text .= $this->getDuration($block->getLength());
            $text .= $this->getFilename($block->getSaveFilename());
//            $text .= $this->getFilename($block->getFilename()->getBasename());
            if ($discontinuity == Block::DISCONTINUITY_NEXT || $discontinuity == Block::DISCONTINUITY_BOTH) {
                $text .= $this->getDiscontinuity();
            }
        }
        $text .= $this->getEndHeader();
        return $text;
    }

    /**
     * @return string
     */
    protected function getStartHeader(): string
    {
        return '#EXTM3U'.PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getVersion(): string
    {
        return '#EXT-X-VERSION:3'.PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getMediaSequence(): string
    {
        return '#EXT-X-MEDIA-SEQUENCE:1'.PHP_EOL;
    }

    /**
     * @param float $max
     * @return string
     */
    protected function getTargetDuration(float $max): string
    {
        return '#EXT-X-TARGETDURATION:'.ceil($max).PHP_EOL;
    }

    /**
     * @param float $length
     * @return string
     */
    protected function getDuration(float $length): string
    {
        return "#EXTINF:$length,".PHP_EOL;
    }

    /**
     * @param string $basename
     * @return string
     */
    protected function getFilename(string $basename): string
    {
        return $basename.PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getDiscontinuity(): string
    {
        return '#EXT-X-DISCONTINUITY'.PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getEndHeader(): string
    {
        return '#EXT-X-ENDLIST'.PHP_EOL;
    }
}