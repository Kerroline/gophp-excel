<?php

namespace Kerroline\PhpGoExcel\Entities\Alignments;

class ReadingOrder
{
    /**
     * Reading order is determined by scanning the text for the first non-whitespace character: 
     * if it is a strong right-to-left character, the reading order is right-to-left; 
     * otherwise, the reading order left-to-right.
     *
     * @var int
     */
    public const CONTEXT_DEPENDS = 0;

    /**
     * Reading order is left-to-right in the cell, as in English.
     *
     * @var int
     */
    public const LEFT_TO_RIGHT   = 1;

    /**
     * Reading order is right-to-left in the cell, as in Hebrew.
     *
     * @var int
     */
    public const RIGHT_TO_LEFT   = 2;
}
