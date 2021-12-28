<?php
namespace App\Test;

use Bob\M3U8\Index\M3U8;
use Bob\M3U8\Filename\Filename;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class M3U8ModelTest extends TestCase
{
    /**
     * @return M3U8
     * @throws GuzzleException
     */
    public function testLoadingM3U8FromUrl(): M3U8
    {
        $address = "https://fu-video3.oss-cn-shenzhen.aliyuncs.com/record/live/61ca88544e641c3b275f3f7b/2021-12-28-11-45-48_2021-12-28-12-15-48_clip_1640666756707082.m3u8";
        $filename = new Filename($address);
        $m3u8 = new M3U8($filename);
        $blocks = $m3u8->getTimeline()->getBlocks();
        $this->assertEquals(1, $blocks[55]->getDiscontinuity());
        $this->assertEquals(1, $blocks[68]->getDiscontinuity());
        $this->assertEquals(1, $blocks[80]->getDiscontinuity());
        $this->assertEquals(1, $blocks[96]->getDiscontinuity());
        return $m3u8;
    }
}