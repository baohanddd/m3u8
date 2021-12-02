<?php
namespace Bob\M3U8\Block;

use Bob\M3U8\Index\M3U8;
use Bob\M3U8\Session;
use Bob\M3U8\Block\Sickle\SickleInvalidIntervalException;
use Bob\M3U8\Block\Sickle\SickleInvalidPositionException;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * 镰刀切割器
 * Class Sickle
 * @package Bob\M3U8\Block\Block
 */
class Sickle
{
    /**
     * 开始剪辑时间
     * @var float
     */
    protected float $start = 0.0;

    /**
     * 结束剪辑时间
     * @var float
     */
    protected float $end = 0.0;

    /**
     * 剪辑的block
     * @var Block
     */
    protected Block $block;

    /**
     * 结束时间和开始时间的间隔
     * @var float
     */
    protected float $interval = 0.0;

    /**
     * 最小间隔
     * @var float
     */
    protected float $min_interval = 3.0;
    
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $log;

    /**
     * Sickle constructor.
     * @param Block $block
     * @param float $start
     * @param float $end
     * @throws SickleInvalidIntervalException
     */
    public function __construct(Block $block, float $start = 0, float $end = 0)
    {
        $this->start = $start;
        $this->end   = $end;
        $this->interval = $end - $start;
        $this->block = $block;
        $this->log = Session::getLog();
        if ($this->interval < 0) {
            $this->log->warning('sickle block occur interval invalid', [
                'start' => $start,
                'end' => $end
            ]);
            throw new SickleInvalidIntervalException($block, $start, $end);
        }
    }

    /**
     * @return Block|null
     * @throws Exception
     */
    public function cut(): ?Block
    {
        $filename = $this->block->getCutFilename();
        
        // when `merge` on the same block and start equals zero...
        if ($this->start == 0 && $this->end == 0) {
            Session::getLog()->debug('start and end are zero both', ["The `end` - `start` == 0 sec of {$filename->getBasename()}"]);
            $timeline = $this->block->getTimeline();
            $index = $timeline->getIndex($this->block);
            if ($index != -1) {
                $timeline->removeBlock($index);
            }
            return null;
        }
        
        if (!$this->tooSmallInterval()) {
            Session::getLog()->debug('skip to cut', ["The `end` - `start` <= {$this->min_interval} sec of {$filename->getBasename()}"]);
            return $this->block;
        }

        $this->ffmpegCommand(
            $filename->toString(),
            $filename->getTemporary(),
            $this->calcPosition($this->start),
            $this->calcPosition($this->end)
        );

        // update block length
        $this->block->setLength($this->end - $this->start);
        return $this->block;
    }
    
    /**
     * @param string $sourceAddress
     * @param string $destinationAddress
     * @param string $sp
     * @param string $ep
     * @return bool
     * @throws Exception
     */
    protected function ffmpegCommand(string $sourceAddress, string $destinationAddress, string $sp, string $ep): bool
    {
        $bin = M3U8::$ffmpeg;
        if (!$bin) throw new Exception('need set $m3u8->setFFMPEG(string $binPath); first...', 500);
        if (!file_exists($bin)) throw new Exception('ffmpeg bin path is invalid...', 500);
        $command = "{$bin} -i {$sourceAddress} -ss {$sp} -to {$ep} -c:a aac -c:v libx264 {$destinationAddress} 2>&1";
        $out = shell_exec($command);

        Session::getLog()->debug('FFMPEG Command', [$command]);
        Session::getLog()->debug('Block Length: '.$this->block->getLength());
        Session::getLog()->debug('Start Position: '.$sp);
        Session::getLog()->debug('End Position: '.$ep);
        Session::getLog()->debug('FFMPEG Filename: '.$sourceAddress);
        Session::getLog()->debug('Temporary File: '.$destinationAddress);
        Session::getLog()->debug('FFMPEG output: '.$out);

        return (bool) $out;
    }

    /**
     * 如果开始和结束间隔小于最小间隔则补齐至最小间隔
     * 优先左推，否则右推，如果无条件左推右推则跳过剪辑
     * @return bool
     */
    public function tooSmallInterval(): bool
    {
        if ($this->interval < $this->min_interval) {
            // 需要移动的秒数
            $seconds = $this->min_interval - $this->interval;

            // 向左推，如果开始时间能够向左推移的话
            if (($this->start - $seconds) > 0) {
                $this->start -= $seconds;
                return true;
            }

            // 向右推，如果结束时间能够向右推移的话
            if (($this->end + $seconds) < $this->block->getLength()) {
                $this->end += $seconds;
                return true;
            }

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param float $seconds
     * @return string 00:00:30
     * @throws SickleInvalidPositionException
     */
    public function calcPosition(float $seconds): string
    {
        $format = function($seconds) {
            return str_pad((string) $seconds,2,'0', STR_PAD_LEFT);
        };
        if ($seconds < 0) {
            throw new SickleInvalidPositionException('start seconds must greater than zero', 400);
        } else if ($seconds <= 60) {
            return "00:00:{$format($seconds)}";
        } else if ($seconds <= 3600) {
            $min = floor($seconds / 60);
            $second = $seconds - ($min * 60);
            return "00:{$format($min)}:{$format($second)}";
        } else if ($seconds <= 86400) {
            $hour = floor($seconds / 60 / 60);
            $min = floor(($seconds - ($hour * 3600)) / 60);
            $second = $seconds - ($hour * 3600) - ($min * 60);
            return "{$format($hour)}:{$format($min)}:{$format($second)}";
        } else {
            throw new SickleInvalidPositionException('start seconds must less than 24h', 400);
        }
    }

    /**
     * @return Block
     */
    public function getBlock(): Block
    {
        return $this->block;
    }

    /**
     * @return float
     */
    public function getStart(): float
    {
        return $this->start;
    }

    /**
     * @return float
     */
    public function getEnd(): float
    {
        return $this->end;
    }

    /**
     * @param float $min_interval
     */
    public function setMinInterval(float $min_interval): void
    {
        $this->min_interval = $min_interval;
    }

    /**
     * @param float $start
     */
    public function setStart(float $start): void
    {
        $this->start = $start;
    }

    /**
     * @param float $end
     */
    public function setEnd(float $end): void
    {
        $this->end = $end;
    }
}