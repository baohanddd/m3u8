<?php
namespace Bob\M3U8\Index;

use Bob\M3U8\Filename\InvalidFilenameAddress;
use Bob\M3U8\Session;
use Bob\M3U8\Filename\Filename;
use Chrisyue\PhpM3u8\Facade\ParserFacade;
use Chrisyue\PhpM3u8\Stream\TextStream;
use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class M3U8
 * @package Bob\M3U8
 */
class M3U8
{
    /**
     * @var Segment[]
     */
    protected array $segments = [];
    
    /**
     * @var Timeline
     */
    protected Timeline $timeline;
    
    /**
     * @var Filename
     */
    protected Filename $filename;
    
    /**
     * @var Closure
     */
    private Closure $indexSaver;
    
    /**
     * @var Closure
     */
    public Closure $blockSaver;
    
    /**
     * hosts who can be clipped.
     * @var array
     */
    private array $hosts = [];
    
    /**
     * save path prefix
     * @var string
     */
    private string $savePath = "/tmp";
    
    /**
     * path of ffmpeg bin file
     * @var string
     */
    public static string $ffmpeg = "/usr/bin/ffmpeg";
    
    /**
     * M3U8 constructor.
     * @example https://fu-video.oss-cn-shanghai.aliyuncs.com/record/live110/en/2021-04-13-17-18-33_2021-04-13-17-20-48.m3u8
     * @param Filename $filename
     * @throws Exception
     * @throws GuzzleException
     */
    public function __construct(Filename $filename)
    {
        $log = Session::getLog();
        $log->debug('Modeling M3U8: '.$filename);
        
        $this->filename = $filename;
        
        $content = $this->getContent($filename);
        if (!$content) throw new M3U8LoadException($filename);
        $log->debug('Load m3u8 file ...ok');
        
        $parser = new ParserFacade();
        $mediaPlaylist = $parser->parse(new TextStream($content));
        $log->debug('parse m3u8 ...ok');
        
        $total = 0;
        if (!isset($mediaPlaylist['mediaSegments']))
            throw new InvalidM3U8Data($filename, $content);
        
        $previous = null;
        $this->timeline = new Timeline($this);
        foreach ($mediaPlaylist['mediaSegments'] as $section) {
            try {
                $segment = new Segment($filename, $section, $previous);
                $this->segments[] = $segment;
                $previous = $segment;
                $total++;
                $this->timeline->addSegment($segment);
            } catch (InvalidSegmentData $e) {
                $log->warning($e->getMessage());
            }
        }
        
        $log->debug("m3u8 have segment total: $total");
    }
    
    /**
     * @param Closure $handler
     */
    public function setIndexSaveHandler(Closure $handler)
    {
        $this->indexSaver = $handler;
    }
    
    /**
     * @param Closure $handler
     */
    public function setBlockSaveHandler(Closure $handler)
    {
        $this->blockSaver = $handler;
    }
    
    /**
     * The path where video clip saved
     * It allows local path or a valid file address on network
     * @example /oss/video3
     * @example /tmp
     * @param string $path
     */
    public function setSavePath(string $path)
    {
        $this->savePath = rtrim($path, '/');
    }
    
    /**
     * @param string $binPath
     */
    public function setFFMPEG(string $binPath)
    {
        static::$ffmpeg = $binPath;
    }
    
    /**
     * @param string $domain
     */
    public function addClippableDomain(string $domain)
    {
        $this->hosts[] = $domain;
    }
    
    /**
     * @param string $host
     * @return bool
     */
    public function isClippableDomain(string $host): bool
    {
        return in_array($host, $this->hosts);
    }
    
    /**
     * 将视频索引文件另存为
     * @return string
     */
    public function saveAs(): string
    {
        $dumper = new Dumper();
        $this->filename->increaseVersion();
        $handler = $this->indexSaver;
        return $handler(
            $this->filename->getUploadName(),
            $dumper->dump($this->timeline)
        );
    }
    
    /**
     * Append another m3u8 file to it
     * @param string $address
     * @return $this
     * @throws InvalidFilenameAddress
     * @throws GuzzleException
     * @throws Exception
     */
    public function append(string $address): M3U8
    {
        $filename = new Filename($address);
        $m3u8 = new M3U8($filename);
        $segments = $m3u8->getSegments();
        $total = count($segments);
        foreach ($segments as $i => $segment) {
            if ($i + 1 == $total) $segment->setDisContinuity(true);
            $this->timeline->addSegment($segment);
        }
        Session::getLog()->debug("Append total of {$total} segments from m3u8 {$address}...");
        return $this;
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
    public function getFilename(): Filename
    {
        return $this->filename;
    }
    
    /**
     * @return string
     */
    public function getSavePath(): string
    {
        return $this->savePath;
    }

    /**
     * @return Segment[]
     */
    public function getSegments(): array
    {
        return $this->segments;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->timeline;
    }
    
    /**
     * @param string $url
     * @return string
     * @throws GuzzleException
     */
    protected function getContent(string $url): string
    {
        $client = new Client([
            'timeout'     => 30.0,
            'verify'      => true,
            'debug'       => false,
            'http_errors' => false
        ]);
        $res = $client->get($url);
        return $res->getBody()->getContents();
    }
}