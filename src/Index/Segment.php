<?php
namespace Bob\M3U8\Index;

use Bob\M3U8\Filename\Filename;

/**
 * Class Segment
 * @package Bob\M3U8\Index
 */
class Segment
{

    /**
     * 文件完整路径
     * @example https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/1618302587_77.ts
     * @var string
     */
    protected string $url = "";

    /**
     * 秒，分段时长
     * @example 16.276
     * @var float
     */
    protected float $duration = 0.0;

    /**
     * @var bool
     */
    protected bool $disContinuity = false;

    /**
     * Segment constructor.
     * @param Filename $filename
     * @param $data
     * @param Segment|null $previous
     * @throws InvalidSegmentData
     */
    public function __construct(Filename $filename, $data, ?Segment $previous = null)
    {
        if (!isset($data['EXTINF'])) throw new InvalidSegmentData('Without EXTINF in Segment raw data');
        if (isset($data['EXT-X-DISCONTINUITY']) && $data['EXT-X-DISCONTINUITY'] && $previous) $previous->disContinuity = true;
        $this->duration = $data['EXTINF']->getDuration();
        if ($this->isCompleteUrl($data['uri'])) {
            $this->url = $data['uri'];
        } else {
            $this->url = $filename->getUrlWithoutFilename() . '/' . $data['uri'];
        }
    }

    /**
     * @param $uri
     * @return bool
     */
    protected function isCompleteUrl($uri): bool
    {
        $parsed = parse_url($uri);
        return isset($parsed['scheme']) && $parsed['scheme'];
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param bool $disContinuity
     */
    public function setDisContinuity(bool $disContinuity): void
    {
        $this->disContinuity = $disContinuity;
    }

    /**
     * @return bool
     */
    public function isDisContinuity(): bool
    {
        return $this->disContinuity;
    }
}