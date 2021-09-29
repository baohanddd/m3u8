<?php
namespace Bob\M3U8\Video\Block\Sickle;

use Exception;

/**
 * Class SickleInvalidIntervalException
 * @package Bob\M3U8\Video\Block\Sickle
 */
class SickleInvalidIntervalException extends Exception
{
    protected $code = 400;
}