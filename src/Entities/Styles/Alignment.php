<?php

namespace Kerroline\PhpGoExcel\Entities\Styles;

use Kerroline\PhpGoExcel\Entities\Styles\Alignments\{
  Horizontal,
  ReadingOrder,
  Vertical,
};

class Alignment
{
  /**
   * All keys witch supported golang excel library
   *
   * @var array
   */
  protected $settings = [
    // 'horizontal'      => '',     // string
    // 'indent'          => 0,     // int
    // 'justifyLastLine' => false,  // bool
    // + 'readingOrder'    => 0,     // uint64
    // 'relativeIndent'  => ,0     // int
    // 'shrinkToFit'     => false,  // bool
    // 'textRotation'    => 0,      // int
    // + 'vertical'        => '',     // string
    // + 'wrapText'        => false,  // bool
  ];

  public static function make(): Alignment
  {
    return new static;
  }

  public function serialize(): array
  {
    return $this->settings;
  }

  public function setWrapText(bool $state = true)
  {
    $this->settings['wrapText'] = $state;

    return $this;
  }

  public function setJustifyLastLine(bool $state = true)
  {
    $this->settings['justifyLastLine'] = $state;

    return $this;
  }

  public function setHorizontal(string $position)
  {
    $availablePosition = [
      Horizontal::LEFT,
      Horizontal::CENTER,
      Horizontal::RIGHT,
      Horizontal::FILL,
      Horizontal::JUSTIFY,
      Horizontal::CENTER_CONTINUOUS,
      Horizontal::DISTRIBUTED,
    ];

    if (!in_array($position, $availablePosition)) {
      throw new \Exception("Invalid alignment horizontal position");
    }

    $this->settings['horizontal'] = $position;

    return $this;
  }

  public function setVertical(string $position)
  {
    $availablePosition = [
      Vertical::TOP,
      Vertical::CENTER,
      Vertical::JUSTIFY,
      Vertical::DISTRIBUTED,
    ];

    if (!in_array($position, $availablePosition)) {
      throw new \Exception("Invalid alignment vertical position");
    }

    $this->settings['vertical'] = $position;

    return $this;
  }

  public function setReadingOrder(int $order)
  {
    $availableOrder = [
      ReadingOrder::CONTEXT_DEPENDS,
      ReadingOrder::LEFT_TO_RIGHT,
      ReadingOrder::RIGHT_TO_LEFT,
    ];

    if (!in_array($order, $availableOrder)) {
      throw new \Exception("Invalid alignment reading order value");
    }

    $this->settings['readingOrder'] = $order;

    return $this;
  }
}
