<?php
namespace Bob\M3U8\Filename;

/**
 * Class Filename
 * @example /www/htdocs/inc/lib.inc.php
 * @package Bob\M3U8\Block
 */
class Filename
{
    /**
     * @example https
     * @var string
     */
    protected string $scheme;
    /**
     * @example fu-video.oss-cn-shanghai.aliyuncs.com
     * @var string
     */
    protected string $host;
    /**
     * @example /record/live110/en/1618305544_96.ts
     * @var string
     */
    protected string $path;
    /**
     * @example /www/htdocs/inc
     * @var string
     */
    protected string $dirname;

    /**
     * @example lib.inc.php
     * @var string
     */
    protected string $basename;

    /**
     * @example php
     * @var string
     */
    protected string $extension;

    /**
     * @example lib.inc
     * @var string
     */
    protected string $filename;

    /**
     * @example xkdl393n2x9382nb
     * @var string
     */
    protected string $tempname;

    /**
     * @example start=0&end=384083&type=mpegts&resolution=1280x720
     * @var string
     */
    protected string $query;

    /**
     * the raw filename
     * @example http://1255625648.vod2.myqcloud.com/3b7472aevodcq1255625648/f096e54a3701925919729431238/2936489898_90547011_1.ts?start=0&end=384083&type=mpegts&resolution=1280x720
     * @var string
     */
    protected string $original;

    /**
     * Filename constructor.
     * @example https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/1618305544_96.ts
     * @param string $filename
     * @throws InvalidFilenameAddress
     */
    public function __construct(string $filename)
    {
        $this->original = $filename;
        $parsed = parse_url($filename);
        $info = pathinfo($parsed['path']);
        if (!isset($parsed['scheme']) || !isset($parsed['host']) || !isset($parsed['path'])) {
            throw new InvalidFilenameAddress(
                "Invalid $filename to parse...");
        }
        $this->scheme = $parsed['scheme'];
        $this->host   = $parsed['host'];
        $this->path   = $parsed['path'];
        $this->query  = $parsed['query']??'';
        $this->dirname   = $info['dirname'];
        $this->basename  = $info['basename'];
        $this->extension = $info['extension'];
        $this->filename  = $info['filename'];
        $this->tempname  = uniqid().'.ts';
    }
    
    /**
     * @example record/live110/en/1618305544_96_1.ts
     */
    public function increaseVersion(): void
    {
        $basename = new Basename($this->basename);
        $basename->increase();
        $this->basename = $basename;
    }
    
    /**
     * @return string
     */
    public function getUrlWithoutFilename(): string
    {
        return "{$this->scheme}://{$this->host}{$this->dirname}";
    }
    
    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getDirname(): string
    {
        return $this->dirname;
    }

    /**
     * @return string
     */
    public function getTemporary(): string
    {
        return '/tmp/'.$this->tempname;
    }

    /**
     * @return string
     */
    public function getBasename(): string
    {
        return $this->basename;
    }

    /**
     * @return string
     */
    public function getUploadName(): string
    {
        $dirname = $this->dirname;
        if ($dirname[0] == '/') $dirname = substr($dirname, 1, strlen($dirname));
        return $dirname.'/'.$this->basename;
    }

    /**
     * @param string $basename
     */
    public function setBasename(string $basename)
    {
        $this->basename = $basename;
    }

    /**
     * Get fully URL
     * @example https://video2.futurelink.live/record/live110/en/1618305544_96.ts
     * @example 2414885906_349185812_1.ts?start=0&end=1143979&type=mpegts&resolution=1280x720
     * @return string
     */
    public function toString(): string
    {
        if ($this->query) {
            return $this->getUrlWithoutFilename() . "/{$this->basename}" . "?" . $this->query;
        } else {
            return $this->getUrlWithoutFilename() . "/{$this->basename}";
        }
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}