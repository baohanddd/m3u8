<?php
use Bob\M3U8\Block\Cuttable;
use Bob\M3U8\Block\Sickle\SickleInvalidIntervalException;
use Bob\M3U8\Index\M3U8;
use Bob\M3U8\Filename\Filename;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class MergeBlocksTest extends TestCase
{
    /**
     * @throws GuzzleException
     * @throws SickleInvalidIntervalException
     */
    public function testMerge()
    {
        $filename = new Filename("https://video3.futurelink.live/record/aliyun/en2/a.m3u8");
        $m3u8 = new M3U8($filename);
        $m3u8->setFFMPEG('/data/bin/ffmpeg');
        $m3u8->setSavePath("/oss/video3");
        $m3u8->addClippableDomain('video3.futurelink.live');
        $result = $m3u8->getTimeline()->merge(20, 70);
        $this->assertInstanceOf(Cuttable::class, $result->cut());
        $this->assertCount(7, $m3u8->getTimeline()->getBlocks());
    }
}