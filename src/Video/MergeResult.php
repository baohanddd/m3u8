<?php
namespace Bob\M3U8\Video;

use Bob\M3U8\Session;
use Bob\M3U8\Video\Block\Sickle;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class MergeResult
 * @package Bob\M3U8\Video
 */
class MergeResult implements Cuttable
{
    /**
     * @var Sickle|null
     */
    protected ?Sickle $start = null;

    /**
     * @var Sickle|null
     */
    protected ?Sickle $end = null;

    /**
     * @var Block[]
     */
    protected array $blocks = [];

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $log;

    /**
     * @var Timeline
     */
    protected Timeline $timeline;

    /**
     * MergeResult constructor.
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
     * @throws Sickle\SickleInvalidIntervalException
     */
    public function merge(): void
    {
        $this->log->debug('blocks are same, starting copy new block...');
        $newBlock = $this->start->getBlock()->copy();
        $newBlock->setDiscontinuity(Block::DISCONTINUITY_BOTH);
        $this->log->debug('copied block file = '.$newBlock->getCutFilename());
        $this->timeline->insert($this->start->getBlock(), $newBlock);
        $this->setEnd(
            new Sickle($newBlock, $this->end->getStart(), $this->end->getEnd())
        );
    }

    /**
     * @return $this
     * @throws Sickle\SickleInvalidIntervalException
     * @throws Exception
     */
    public function cut(): Cuttable
    {
        if ($this->start && $this->end) {
            if ($this->isSame()) {
                $this->merge();
            }
            if ($this->start) {
                $this->addBlocks($this->start->cut());
            }
            if ($this->end) {
                $this->addBlocks($this->end->cut());
            }
        } elseif ($this->end) {
            $this->addBlocks($this->end->cut());
        } elseif ($this->start) {
            $this->addBlocks($this->start->cut());
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