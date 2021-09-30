<?php
namespace Bob\M3U8\Block\Sickle;

use Bob\M3U8\Block\Block;
use Exception;

/**
 * Class SickleInvalidIntervalException
 * @package Bob\M3U8\Block\Block\Sickle
 */
class SickleInvalidIntervalException extends Exception
{
    protected $code = 400;
    
    public function __construct(Block $block, float $start, float $end)
    {
        $message = "`start`({$start}) should less than `end`({$end}) while be cropping block called `{$block}`...";
        parent::__construct($message, $this->code);
    }
}