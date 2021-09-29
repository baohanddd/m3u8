<?php
namespace App\Test;

use Bob\M3U8\Index\M3U8;
use Bob\M3U8\Session;
use Bob\M3U8\Video\Filename;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class M3U8Test extends TestCase
{
    /**
     * @return M3U8
     * @throws GuzzleException
     */
    public function testLoadingM3U8FromUrl(): M3U8
    {
        $filename = new Filename("https://video3.futurelink.live/record/aliyun/en2/a.m3u8");
        $m3u8 = new M3U8($filename);
        $this->assertCount(8, $m3u8->getSegments());
        $this->assertCount(8, $m3u8->getTimeline()->getBlocks());
        return $m3u8;
    }
    
    /**
     * @param M3U8 $m3u8
     * @depends testLoadingM3U8FromUrl
     */
    public function testSaveAs(M3U8 $m3u8)
    {
        $uploader = function(string $uploadName, string $content): string {
            Session::getLog()->debug("m3u8 content: {$content}");
            return "https://play.futurelink.live/{$uploadName}";
        };
        $url = $m3u8->saveAs($uploader);
        $this->assertStringContainsString('play.futurelink.live', $url);
    }
    
    /**
     * @param M3U8 $m3u8
     * @throws Filename\InvalidFilenameAddress
     * @throws GuzzleException
     * @depends testLoadingM3U8FromUrl
     */
    public function testAppend(M3U8 $m3u8)
    {
        $address = "https://playback2.futurelink.live/3b7472aevodcq1255625648/3dc321e63701925925148783052/playlist_eof.m3u8";
        $m3u8->append($address);
        $this->assertCount(579, $m3u8->getTimeline()->getBlocks());
    }
    
    /**
     * @throws GuzzleException
     * @throws \Bob\M3U8\Video\Block\Sickle\SickleInvalidIntervalException
     */
    public function testClip()
    {
        $filename = new Filename("https://video3.futurelink.live/record/aliyun/en2/a.m3u8");
        $m3u8 = new M3U8($filename);
        $result = $m3u8->getTimeline()->clip(10, 20);
        $this->assertTrue($result->cut()->saveAs());
//        $sickle = $result->merge();
//        $block = $sickle->cut();
//        $this->assertTrue($result->isSame());
//        $this->assertEquals(10, $sickle->getStart());
//        $this->assertEquals(20, $sickle->getEnd());
//        $this->assertCount(1, $m3u8->getTimeline()->getBlocks());
//        $this->assertEquals(10, $block->getLength());
    }
}