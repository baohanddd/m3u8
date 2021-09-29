<?php
namespace Bob\M3U8\Video\Block\Sickle;

use Exception;

/**
 * Class SickleInvalidPositionException
 * @package Bob\M3U8\Video\Block\Sickle
 */
class SickleInvalidPositionException extends Exception
{
    protected $code = 400;
}