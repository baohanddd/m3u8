<?php
namespace Bob\M3U8\Filename;

use Bob\M3U8\Component\MicroTime;

/**
 * Class Basename
 * @example 1618305544_96_1589298812510100.ts
 * @example merge-2021-04-23-08-22-30.m3u8
 * @package Bob\M3U8\Filename
 */
class Basename
{
    /**
     * @example 1618305544_96_1
     * @var string
     */
    protected string $filename = "";
    
    /**
     * @example 1618305544311052
     * @var string
     */
    protected string $clipname = "";
    
    /**
     * @example _copy
     * @var string
     */
    protected string $suffix = "";
    
    /**
     * @example ts
     * @var string
     */
    protected string $extension = "";

    /**
     * Basename constructor.
     * @example 1618305544_96.ts
     * @param string $basename
     */
    public function __construct(string $basename)
    {
        $info = pathinfo($basename);
        $this->setFilename($info['filename']);
        $this->extension = $info['extension'];
    }
    
    /**
     * @param string $filename
     */
    protected function setFilename(string $filename): void
    {
        if (preg_match('/_clip_[0-9]{16}/i', $filename, $match) !== 0) {
            $this->clipname = $match[0];
            $filename = preg_replace('/_clip_[0-9]{16}/i', '', $filename);
        }
        if (preg_match('/_copy/i', $filename, $match) !== 0) {
            $this->suffix = $match[0];
            $filename = preg_replace('/_copy/i', '', $filename);
        }
        $this->filename = $filename;
    }
    
    /**
     * generate a unique string name for new file
     * It will avoid overwrite original file if saved
     */
    public function increase(): void
    {
        $this->clipname = MicroTime::create();
    }

    /**
     * @param string $suffix
     */
    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->filename}_clip_{$this->clipname}{$this->suffix}.{$this->extension}";
    }
}