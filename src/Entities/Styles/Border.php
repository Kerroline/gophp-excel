<?php

namespace Kerroline\PhpGoExcel\Entities\Styles;

class Border
{
    // public const POSITION_NONE   = 'none';
    public const POSITION_LEFT   = 'left';
    public const POSITION_RIGHT  = 'right';
    public const POSITION_TOP    = 'top';
    public const POSITION_BOTTOM = 'bottom';
    // public const POSITION_ALL    = 'all';

    public const STYLE_NONE               = 0;
    public const STYLE_NORMAL             = 1;
    public const STYLE_NORMAL_BOLD        = 2;
    public const STYLE_DASH               = 3;
    public const STYLE_DOT                = 4;
    public const STYLE_NORMAL_VERY_BOLD   = 5;
    public const STYLE_DOUBLE             = 6;
    public const STYLE_SMALL_DOT          = 7;
    public const STYLE_DASH_BOLD          = 8;
    public const STYLE_DASH_DOT           = 9;
    public const STYLE_DASH_DOT_BOLD      = 10;
    public const STYLE_DASH_DOT_DOT       = 11;
    public const STYLE_DASH_DOT_DOT_BOLD  = 12;
    public const STYLE_SLANT_DASH_DOT     = 13;


    /**
     * Undocumented variable
     *
     * Available: [
     *  'left',
     *  'right',
     *  'top',
     *  'bottom',
     *  'diagonalDown',
     *  'diagonalUp',
     * ]
     *
     * @var array
     */
    protected $settings = [
        // 'type'  => '',
        // 'color' => '',
        // 'style' => 0,
    ];

    protected function __construct()
    {
        // $this->position = 'distributed';
    }

    public static function make(): Border
    {
        return new static;
    }

    public function serialize(): array
    {
        return $this->settings;
    }

    public function setType(string $type)
    {
        $availableTypes = [
            Border::POSITION_LEFT,
            Border::POSITION_RIGHT,
            Border::POSITION_TOP,
            Border::POSITION_BOTTOM,
            // Border::ALL,
        ];

        if (!in_array($type, $availableTypes)) {
            throw new \Exception("Invalid border type");
        }

        $this->settings['type'] = $type;

        return $this;
    }

    public function setColorHex(string $color)
    {
        //TODO: Border Hex color validation
        $this->settings['color'] = $color;

        return $this;
    }

    public function setStyle(int $style)
    {
        $availableStyles = [
            Border::STYLE_NONE,
            Border::STYLE_NORMAL,
            Border::STYLE_NORMAL_BOLD,
            Border::STYLE_DASH,
            Border::STYLE_DOT,
            Border::STYLE_NORMAL_VERY_BOLD,
            Border::STYLE_DOUBLE,
            Border::STYLE_SMALL_DOT,
            Border::STYLE_DASH_BOLD,
            Border::STYLE_DASH_DOT,
            Border::STYLE_DASH_DOT_BOLD,
            Border::STYLE_DASH_DOT_DOT,
            Border::STYLE_DASH_DOT_DOT_BOLD,
            Border::STYLE_SLANT_DASH_DOT,
        ];

        if (!in_array($style, $availableStyles)) {
            throw new \Exception("Invalid border style");
        }

        $this->settings['style'] = $style;

        return $this;
    }
}
