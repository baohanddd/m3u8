<?php
namespace Bob\M3U8\Block;

/**
 * 可剪切block并持久化的接口
 * Interface Cuttable
 * @package Bob\M3U8\Block
 */
interface Cuttable
{
    /**
     * @return Cuttable
     */
    public function cut(): Cuttable;

    /**
     * @return bool
     */
    public function saveAs(): bool;
}