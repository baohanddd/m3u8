<?php
namespace App\Test;

use Bob\M3U8\Filename\InvalidFilenameAddress;
use Bob\M3U8\Index\M3U8;
use Bob\M3U8\Filename\Filename;
use Bob\M3U8\Session;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class M3U8AppendTest extends TestCase
{
    protected $addresses = [
        "https://video3.futurelink.live/record/live/housecard12/2021-12-27-15-22-42_2021-12-27-15-26-54.m3u8",
        "https://playback2.futurelink.live/3b7472aevodcq1255625648/3dc321e63701925925148783052/playlist_eof.m3u8"
    ];
    /**
     * @return M3U8
     * @throws GuzzleException
     */
    public function testLoadingM3U8FromUrl(): M3U8
    {
        $filename = new Filename($this->addresses[0]);
        $m3u8 = new M3U8($filename);
        $blocks = $m3u8->getTimeline()->getBlocks();
        $this->assertCount(8, $blocks);
        $this->assertEquals(1, $blocks[7]->getDiscontinuity());
        return $m3u8;
    }
    
    /**
     * @param M3U8 $m3u8
     * @return M3U8
     * @throws GuzzleException
     * @throws InvalidFilenameAddress
     * @depends testLoadingM3U8FromUrl
     */
    public function testFirstAppend(M3U8 $m3u8): M3U8
    {
        $m3u8->append($this->addresses[0]);
        $blocks = $m3u8->getTimeline()->getBlocks();
        $this->assertCount(16, $blocks);
        $this->assertEquals(1, $blocks[7]->getDiscontinuity());
        $this->assertEquals(1, $blocks[15]->getDiscontinuity());
        return $m3u8;
    }
    
    /**
     * @param M3U8 $m3u8
     * @return M3U8
     * @throws GuzzleException
     * @throws InvalidFilenameAddress
     * @depends testFirstAppend
     */
    public function testSecondAppend(M3U8 $m3u8): M3U8
    {
        $m3u8->append($this->addresses[0]);
        $blocks = $m3u8->getTimeline()->getBlocks();
        $this->assertCount(24, $blocks);
        $this->assertEquals(1, $blocks[7]->getDiscontinuity());
        $this->assertEquals(1, $blocks[15]->getDiscontinuity());
        $this->assertEquals(1, $blocks[23]->getDiscontinuity());
        return $m3u8;
    }
    
    /**
     * @param M3U8 $m3u8
     * @depends testSecondAppend
     */
    public function testSaveAs(M3U8 $m3u8)
    {
        $filename = 'test.m3u8';
        $uploader = function(string $uploadName, string $content) use ($filename): string {
            $fp = fopen($filename, 'wb');
            fwrite($fp, $content);
            fclose($fp);
            Session::getLog()->notice("m3u8 content: {$content}");
            return $filename;
        };
        $m3u8->setIndexSaveHandler($uploader);
        $url = $m3u8->saveAs();
        $this->assertEquals($filename, $url);
    }
}