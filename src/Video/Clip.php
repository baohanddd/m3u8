<?php
namespace Bob\M3U8\Video;

use Bob\M3U8\Session;
use Bob\M3U8\Video\Block\Sickle;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * 切割block，保留中间选定的部分
 * Class Clip
 * @package Bob\M3U8\Video
 */
class Clip implements Clippable
{
    /**
     * @var Timeline
     */
    protected Timeline $timeline;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $log;

    /**
     * Clip constructor.
     * @param Timeline $timeline
     */
    public function __construct(Timeline $timeline)
    {
        $this->timeline = $timeline;
        $this->log = Session::getLog();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function clip(float $start, float $end): Cuttable
    {
        $this->invalidPoints($start, $end);
        $this->outOfTimelineLength($start);
        $this->outOfTimelineLength($end);

        $result = new ClipResult();

        $pass = 0.0;
        foreach ($this->timeline->getBlocks() as $idx => $block) {
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
                $this->timeline->removeBlock($idx);
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
                    $this->timeline->removeBlock($idx);
                }
            }
        }
        $this->timeline->reIndexBlocks();
        return $result;
    }

    /**
     * @param float $point
     * @throws Exception
     */
    protected function outOfTimelineLength(float $point)
    {
        if ($point > $this->timeline->getLength()) {
            throw new Exception(
                "The clip point({$point}) must less than or equals total of length ({$this->timeline->getLength()})...",
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