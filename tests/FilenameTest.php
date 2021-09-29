<?php
namespace App\Test;

use Bob\M3U8\Video\Filename;
use PHPUnit\Framework\TestCase;

class FilenameTest extends TestCase
{
    /**
     * @return Filename
     */
    public function testFilename(): Filename
    {
        $filename = new Filename("https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/1618305544_96.ts");
        $this->assertEquals('https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en', $filename->getUrlWithoutFilename());
        $this->assertEquals('fu-video.oss-cn-shanghai.aliyuncs.com', $filename->getHost());
        $this->assertEquals('/record/live110/en', $filename->getDirname());
        $this->assertMatchesRegularExpression('/\/tmp\/[0-9a-f]{6,}\.ts/', $filename->getTemporary());
        $this->assertEquals('1618305544_96.ts', $filename->getBasename());
        $this->assertEquals('record/live110/en/1618305544_96.ts', $filename->getUploadName());
        $this->assertEquals('https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/1618305544_96.ts', $filename);
        $filename->increaseVersion();
        return $filename;
    }
    
    /**
     * @param Filename $filename
     * @return Filename
     * @depends testFilename
     */
    public function testIncreasedVersionFilename(Filename $filename): Filename
    {
        $this->assertMatchesRegularExpression(
            '/https:\/\/fu-video\.oss-cn-shanghai\.aliyuncs\.com\/record\/live110\/en\/1618305544_96_clip_[0-9]{16}\.ts/i',
            $filename
        );
        $this->assertEquals('fu-video.oss-cn-shanghai.aliyuncs.com', $filename->getHost());
        $this->assertEquals('/record/live110/en', $filename->getDirname());
        $this->assertMatchesRegularExpression('/\/tmp\/[0-9a-f]{6,}\.ts/', $filename->getTemporary());
        $this->assertMatchesRegularExpression('/1618305544_96_clip_[0-9]{16}\.ts/', $filename->getBasename());
        return $filename;
    }
}