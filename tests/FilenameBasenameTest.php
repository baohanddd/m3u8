<?php
namespace App\Test;

use Bob\M3U8\Video\Filename;
use PHPUnit\Framework\TestCase;

class FilenameBasenameTest extends TestCase
{
    /**
     * @return Filename\Basename
     */
    public function testIncreaseAndSuffix(): Filename\Basename
    {
        $basename = new Filename\Basename("customize_filename.ts");
        $basename->increase();
        $this->assertMatchesRegularExpression(
            '/customize_filename_clip_[0-9]{16}\.ts/', $basename);
        $basename->setSuffix('_copy');
        $this->assertMatchesRegularExpression(
            '/customize_filename_clip_[0-9]{16}_copy\.ts/', $basename);
        return $basename;
    }
    
    /**
     * @depends testIncreaseAndSuffix
     * @param Filename\Basename $basename
     */
    public function testClippedBasename(Filename\Basename $basename)
    {
        // customize_filename_clip_1632877945318919_copy.ts
        $basename = new Filename\Basename($basename);
        $basename->increase();
        $this->assertMatchesRegularExpression(
            '/customize_filename_clip_[0-9]{16}_copy\.ts/', $basename);
        $basename->setSuffix('_copy');
        $this->assertMatchesRegularExpression(
            '/customize_filename_clip_[0-9]{16}_copy\.ts/', $basename);
    }
}