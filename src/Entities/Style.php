<?php

namespace Kerroline\PhpGoExcel\Entities;

use Kerroline\PhpGoExcel\Interfaces\SerializableEntityInterface;
use Kerroline\PhpGoExcel\Entities\Styles\{
    Alignment,
    Border,
    Font,
};

class Style implements SerializableEntityInterface
{
    /**
     * @var Alignment
     */
    protected $alignment;

    /**
     * @var Font
     */
    protected $font;

    /**
     * @var array<Border>
     */
    protected $borders;

    /**
     * @var int
     */
    protected $numberFormat;

    /**
     * @var string
     */
    protected $customNumberFormat;

    private function __construct()
    {
    }

    public static function make()
    {
        return new static;
    }

    public function serialize(): array
    {
        $serialized = [];

        if (isset($this->alignment)) {
            $serialized['alignment'] = $this->alignment->serialize();
        }

        if (isset($this->font)) {
            $serialized['font'] = $this->font->serialize();
        }

        if (isset($this->borders)) {
            foreach ($this->borders as $border) {
                $serialized['borders'][] = $border->serialize();
            }
        }

        if (isset($this->numberFormat)) {
            $serialized['numberFormat'] = $this->numberFormat;
        }

        if (isset($this->customNumberFormat)) {
            $serialized['customNumberFormat'] = $this->customNumberFormat;
        }

        return $serialized;
    }


    public function setAlignment(Alignment $alignment)
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function setFont(Font $font)
    {
        $this->font = $font;

        return $this;
    }

    public function setBorders(array $borders)
    {
        foreach ($borders as $border) {
            if (!$border instanceof Border) {
                throw new \Exception("Border must be instanceof class GoExcel/Border");
            }
        }

        $this->borders = $borders;

        return $this;
    }

    public function setNumberFormat(int $numberFormat)
    {
        //TODO: Style number format Validate
        $this->numberFormat = $numberFormat;

        return $this;
    }

    public function setCustomNumberFormat(string $customFormat)
    {
        //TODO: Style custom format Validate
        $this->customNumberFormat = $customFormat;

        return $this;
    }
}
