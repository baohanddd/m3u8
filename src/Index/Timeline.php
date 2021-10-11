<?php
namespace Bob\M3U8\Index;

use Bob\M3U8\Filename\Filename;
use Bob\M3U8\Session;
use Bob\M3U8\Block\Block;
use Bob\M3U8\Block\Sickle;
use Bob\M3U8\Block\ClipResult;
use Bob\M3U8\Block\MergeResult;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class Timeline
 * @package Bob\M3U8\Block
 */
class Timeline
{
    /**
     * @var Block[]
     */
    protected array $blocks = [];

    /**
     * The total of length of blocks
     * @var float
     */
    protected float $length = 0.0;
    
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $log;
    
    /**
     * @var M3U8
     */
    private M3U8 $m3U8;
    
    /**
     * @param M3U8 $m3u8
     */
    public function __construct(M3U8 $m3u8)
    {
        $this->log = Session::getLog();
        $this->m3u8 = $m3u8;
    }

    /**
     * @param Segment $segment
     * @throws Exception
     */
    public function addSegment(Segment $segment): void
    {
        $length = $segment->getDuration();
        $block = new Block($this, new Filename($segment->getUrl()), $length);
        if ($segment->isDisContinuity()) $block->setDiscontinuity(Block::DISCONTINUITY_NEXT);
        $this->blocks[] = $block;
    }
    
    /**
     * @param float $start
     * @param float $end
     * @return ClipResult
     * @throws Sickle\SickleInvalidIntervalException
     * @throws Exception
     */
    public function clip(float $start, float $end): ClipResult
    {
        $this->invalidPoints($start, $end);
        $this->outOfTimelineLength($start);
        $this->outOfTimelineLength($end);
        
        $result = new ClipResult($this);
        
        $pass = 0.0;
        foreach ($this->getBlocks() as $idx => $block) {
            $prev  = $pass;
            $pass += $block->getLength();
            $this->log->debug('prev = '.$prev);
            $this->log->debug('pass = '.$pass);
            if ($pass > $start) {
                if ($prev < $start) {
                    $block->setDiscontinuity(Block::DISCONTINUITY_BOTH);
                    // sickle_start
                    $this->log->debug('the start block: '.$block->getCutFilename()->toString());
                    $result->setStart(
                        new Sickle($block, ($start - $prev), $block->getLength())
                    );
                }
            } else {
                $this->removeBlock($idx);
            }
            if ($pass > $end) {
                if ($prev < $end) {
                    $block->setDiscontinuity(Block::DISCONTINUITY_BOTH);
                    // sickle_end
                    $this->log->debug('the end block: '.$block->getCutFilename()->toString());
                    $result->setEnd(
                        new Sickle($block, 0, ($end - $prev))
                    );
                } else {
                    $this->removeBlock($idx);
                }
            }
        }
        $this->reIndexBlocks();
        return $result;
    }
    
    /**
     * @param float $start
     * @param float $end
     * @return MergeResult
     * @throws Sickle\SickleInvalidIntervalException
     * @throws Exception
     */
    public function merge(float $start, float $end): MergeResult
    {
        $this->invalidPoints($start, $end);
        $this->outOfTimelineLength($start);
        $this->outOfTimelineLength($end);
        
        $result = new MergeResult($this);
        
        $pass = 0.0;
        foreach ($this->getBlocks() as $idx => $block) {
            $prev  = $pass;
            $pass += $block->getLength();
            $this->log->debug('prev = '.$prev);
            $this->log->debug('pass = '.$pass);
            
            if ($prev >= $start && $pass <= $end) {
                $this->removeBlock($idx);
                continue;
            }
            
            if ($pass >= $start) {
                if ($prev <= $start) {
                    $block->setDiscontinuity(Block::DISCONTINUITY_BOTH);
                    // sickle_start
                    $result->setStart(
                        new Sickle($block, 0, ($start - $prev))
                    );
                }
            }
            if ($pass >= $end) {
                if ($prev <= $end) {
                    $block->setDiscontinuity(Block::DISCONTINUITY_BOTH);
                    // sickle_end
                    $result->setEnd(
                        new Sickle($block, ($end - $prev), $block->getLength())
                    );
                }
            }
        }
        $this->reIndexBlocks();
        return $result;
    }

    /**
     * @param Block $beforeBlock
     * @param Block $newBlock
     * @return bool
     */
    public function insert(Block $beforeBlock, Block $newBlock): bool
    {
        foreach ($this->blocks as $idx => $block) {
            if ($block === $beforeBlock) {
                array_splice($this->blocks, $idx + 1, 0, [$newBlock]);
                return true;
            }
        }
        return false;
    }

    /**
     * @return Block[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
    
    /**
     * It found nothing when return -1
     * @param Block $block
     * @return int
     */
    public function getIndex(Block $block): int
    {
        foreach ($this->blocks as $index => $b) {
            if ($block === $b) return $index;
        }
        return -1;
    }

    /**
     * Remove block by index
     * @param int $index
     */
    public function removeBlock(int $index)
    {
        unset($this->blocks[$index]);
    }

    /**
     * reIndex blocks after block removed
     */
    public function reIndexBlocks()
    {
        $this->blocks = array_values($this->blocks);
    }

    /**
     * @return float
     */
    public function getMaxLength(): float
    {
        $max = 0.0;
        foreach ($this->blocks as $block) {
            $length = $block->getLength();
            if ($length > $max) $max = $length;
        }
        return $max;
    }

    /**
     * @return float
     */
    public function getLength(): float
    {
        $length = 0;
        foreach ($this->blocks as $block) {
            $length += $block->getLength();
        }
        return $length;
    }
    
    /**
     * @return M3U8
     */
    public function getM3u8(): M3U8
    {
        return $this->m3u8;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        $dumper = new Dumper();
        return $dumper->dump($this);
    }
    
    /**
     * @param float $point
     * @throws Exception
     */
    protected function outOfTimelineLength(float $point)
    {
        if ($point > $this->getLength()) {
            throw new Exception(
                "The crop point({$point}) must less than or equals total of length ({$this->getLength()})...",
                400);
        }
    }
    
    /**
     * @param float $start
     * @param float $end
     * @throws Exception
     */
    protected function invalidPoints(float $start, float $end)
    {
        if ($start >= $end) {
            throw new Exception(
                "The `start`({$start}) must less than `end`({$end})...",
                400);
        }
    }
}