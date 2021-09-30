<?php
namespace App\Test;

use Bob\M3U8\Filename\Basename;
use PHPUnit\Framework\TestCase;

class FilenameBasenameTest extends TestCase
{
    /**
     * @return Basename
     */
    public function testIncreaseAndSuffix(): Basename
    {
        $basename = new Basename("customize_filename.ts");
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
     * @param Basename $basename
     */
    public function testClippedBasename(Basename $basename)
    {
        // customize_filename_clip_1632877945318919_copy.ts
        $basename = new Basename($basename);
        $basename->increase();
        $this->assertMatchesRegularExpression(
            '/customize_filename_clip_[0-9]{16}_copy\.ts/', $basename);
        $basename->setSuffix('_copy');
        $this->assertMatchesRegularExpression(
            '/customize_filename_clip_[0-9]{16}_copy\.ts/', $basename);
    }
}