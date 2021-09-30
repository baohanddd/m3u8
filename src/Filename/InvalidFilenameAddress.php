<?php
namespace Bob\M3U8\Filename;

use Exception;

/**
 * Class InvalidFilenameAddress
 * @package Bob\M3U8\Filename
 */
class InvalidFilenameAddress extends Exception
{
    /**
     * @var int
     */
    protected $code = 400;
}