<?php
namespace Bob\M3U8\Block;

use Bob\M3U8\Index\Timeline;
use Bob\M3U8\Session;
use Exception;

/**
 * Class ClipResult
 * @package Bob\M3U8\Block
 */
class ClipResult implements Cuttable
{
    /**
     * @var Sickle
     */
    protected $start;

    /**
     * @var Sickle
     */
    protected $end;

    /**
     * @var Block[]
     */
    protected array $blocks = [];
    
    /**
     * @var Timeline
     */
    protected Timeline $timeline;
    
    /**
     * ClipResult constructor.
     * @param Timeline $timeline
     */
    public function __construct(Timeline $timeline)
    {
        $this->log = Session::getLog();
        $this->timeline = $timeline;
    }

    /**
     * @return bool
     */
    public function isSame(): bool
    {
        return $this->start && ($this->start->getBlock() === $this->end->getBlock());
    }

    /**
     * merge two sickles if have same block
     * @return Sickle
     * @throws Sickle\SickleInvalidIntervalException
     */
    public function merge(): Sickle
    {
        $block = $this->start->getBlock();
        $block->setDiscontinuity(Block::DISCONTINUITY_BOTH);
        return new Sickle($block, $this->start->getStart(), $this->end->getEnd());
    }

    /**
     * @return $this
     * @throws Sickle\SickleInvalidIntervalException
     * @throws Exception
     */
    public function cut(): Cuttable
    {
        if ($this->isSame()) {
            $this->addBlocks($this->merge()->cut());
        } else {
            if ($this->start) {
                $this->addBlocks($this->start->cut());
            }
            if ($this->end) {
                $this->addBlocks($this->end->cut());
            }
        }
        return $this;
    }
    
    /**
     * @param Block|null $block
     * @return int
     */
    protected function addBlocks(?Block $block): int
    {
        if ($block) $this->blocks[] = $block;
        return count($this->blocks);
    }
    
    /**
     * @return bool
     * @throws Exception
     */
    public function saveAs(): bool
    {
        $blockSaver = $this->timeline->getM3u8()->blockSaver;
        if (!$blockSaver) {
            throw new Exception('employ $m3u8->setBlockSaveHandler() first before save block...', 500);
        }
        foreach ($this->blocks as $block) {
            $block->saveAs($blockSaver);
        }
        return true;
    }

    /**
     * @param Sickle $start
     */
    public function setStart(Sickle $start): void
    {
        if ($start->getBlock()->clippable()) {
            $this->start = $start;
        }
    }

    /**
     * @param Sickle $end
     */
    public function setEnd(Sickle $end): void
    {
        if ($end->getBlock()->clippable()) {
            $this->end = $end;
        }
    }
}