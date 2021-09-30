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
        $this->timeline = new Timeline();
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
     * 将视频索引文件另存为
     * @param Closure $uploader
     * @return string
     */
    public function saveAs(Closure $uploader): string
    {
        $dumper = new Dumper();
        $this->filename->increaseVersion();
        return $uploader(
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