<?php
namespace Bob\M3U8\Block;

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
     */
    public function saveAs(): bool
    {
        foreach ($this->blocks as $block) {
            $block->saveAs(Session::getBlockUploader());
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

    /**
     * @return Block[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * @return Sickle|null
     */
    public function getStart(): ?Sickle
    {
        return $this->start;
    }

    /**
     * @return Sickle|null
     */
    public function getEnd(): ?Sickle
    {
        return $this->end;
    }
}