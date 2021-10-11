<?php
namespace Bob\M3U8\Block;

use Bob\M3U8\Session;
use Bob\M3U8\Index\Timeline;
use Bob\M3U8\Filename\Filename;
use Bob\M3U8\Filename\InvalidFilenameAddress;

/**
 * Class Block
 * @package Bob\M3U8\Block
 */
class Block
{
    /**
     * @example https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/1618305544_96.ts
     * @var Filename
     */
    protected Filename $cutFilename;
    
    /**
     * @example https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/1618305544_96.ts
     * @var Filename
     */
    protected Filename $saveFilename;

    /**
     * @var float
     */
    protected float $length = 0.0;

    /**
     * 是否不连续, 0表示前后相连blocks是连续的
     * @var int
     */
    protected int $discontinuity = 0;

    /**
     * @var Timeline
     */
    protected Timeline $timeline;

    /**
     * 和前后block都连续
     */
    const DISCONTINUITY_NONE = 0;
    /**
     * 和前一个block不连续
     */
    const DISCONTINUITY_PREV = 2;
    /**
     * 和后一个block不连续
     */
    const DISCONTINUITY_NEXT = 1;
    /**
     * 和前后block都不连续
     */
    const DISCONTINUITY_BOTH = 3;

    /**
     * Block constructor.
     * @param Timeline $timeline
     * @param Filename $filename https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/1618305544_96.ts
     * @param float $length
     */
    public function __construct(Timeline $timeline, Filename $filename, float $length = 0)
    {
        $this->timeline = $timeline;
        $this->cutFilename = $filename;
        $this->saveFilename = $filename;
        $this->length   = $length;
    }

    /**
     * @param Block $block
     */
    public function insert(Block $block)
    {
        $this->timeline->insert($this, $block);
    }
    
    /**
     * @return Block
     * @throws InvalidFilenameAddress
     */
    public function copy(): Block
    {
        Session::getLog()->debug('original Cut filename = '.$this->cutFilename);
        $block = new Block($this->timeline, new Filename($this->cutFilename), $this->length);
        $block->saveFilename = new Filename($this->saveFilename);
        return $block;
    }

    /**
     * @return Timeline
     */
    public function getTimeline(): Timeline
    {
        return $this->timeline;
    }

    /**
     * @return Filename
     */
    public function getCutFilename(): Filename
    {
        return $this->cutFilename;
    }

    /**
     * @return Filename
     */
    public function getSaveFilename(): Filename
    {
        return $this->saveFilename;
    }

    /**
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @param float $length
     */
    public function setLength(float $length): void
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getDiscontinuity(): int
    {
        return $this->discontinuity;
    }

    /**
     * @param int $discontinuity
     */
    public function setDiscontinuity(int $discontinuity): void
    {
        $this->discontinuity = $discontinuity;
    }

    /**
     * @return string URL of block
     */
    public function saveAs(\Closure $uploader): string
    {
        $tempName   = $this->cutFilename->getTemporary();
                      if (!file_exists($tempName)) return "";
                      $this->saveFilename->increaseVersion();
        $uploadName = $this->saveFilename->getUploadName();
        
        return $uploader($uploadName, $tempName);

//        AlibabaSdk::getOssClient()->uploadFile($_ENV['VideoBucket'], $uploadName, $tempName);

//        Session::getLog()->debug('Temporary Name: ', [$tempName]);
//        Session::getLog()->debug('Upload OK...', [$uploadName]);
        
//        return $url;

//        return "https://{$_ENV['PlaybackDomain']}/{$uploadName}";
    }

    /**
     * Only process blocks on video3.futurelink.live
     * pass by blocks on playback.futurelink.live
     * @return bool
     */
    public function clippable(): bool
    {
        $host = $this->cutFilename->getHost();
        Session::getLog()->debug("block domain: {$host}");
        return $this->timeline->getM3u8()->isClippableDomain($host);
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->cutFilename;
    }
}