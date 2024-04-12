<?php

namespace Kerroline\PhpGoExcel\Entities\Styles;

use Kerroline\PhpGoExcel\Interfaces\SerializableEntityInterface;

class Font implements SerializableEntityInterface
{
    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $settings = [
        // 'bold'          => false,
        // 'italic'        => false,
        // 'underline'     => '',
        // 'family'        => '',
        // 'size'          => 0,
        // 'strike'        => false,
        // 'color'         => '',
        // 'colorIndexed'  => 0,
        // 'colorTheme'    => 0,
        // 'colorTint'     => 0.0,
        // 'vertAlign'     => '',
    ];

    public static function make(): Font
    {
        return new static;
    }

    public function serialize(): array
    {
        return $this->settings;
    }

    public function setBold(bool $state = true)
    {
        $this->settings['bold'] = $state;

        return $this;
    }

    public function setItalic(bool $state = true)
    {
        $this->settings['italic'] = $state;

        return $this;
    }

    public function setFamily(string $family)
    {
        $this->settings['family'] = $family;

        return $this;
    }

    public function setSize(int $size)
    {
        if ($size <= 0) {
            throw new \Exception("Invalid font size value");
        }

        $this->settings['size'] = $size;

        return $this;
    }
}
