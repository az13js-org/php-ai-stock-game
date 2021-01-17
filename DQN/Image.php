<?php
namespace DQN;

use \Exception;

/**
 * 图像
 */
class Image
{
    /**
     * @var float[]
     */
    private $originPrices;

    /** @var bool */
    private $isKeep;

    /** @var bool */
    private $buyPrice;

    /**
     * 传入数组构造当前价格图像
     *
     * @param float[] $prices
     * @param bool $isKeep
     * @param float $buyPrice
     */
    public function __construct(array $prices, bool $isKeep, float $buyPrice)
    {
        $this->originPrices = $prices;
        $this->isKeep = $isKeep;
        $this->buyPrice = $buyPrice;
    }

    /**
     * 转换为元素值在-1和1之间的数组
     *
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->originPrices)) {
            throw new Exception('Error, no value, image-array=' . print_r($this->originPrices, true));
        }
        $a = min($this->originPrices);
        $b = max($this->originPrices);
        $c = $b - $a; // c >= 0
        if (0 == $c) {
            return array_fill(0, count($this->originPrices, COUNT_NORMAL), 0);
        }
        $prices = array_map(function(float $val) use ($a, $c) {
            return ($val - $a) / $c * 2 - 1;
        }, array_merge([$this->isKeep ? $this->buyPrice : $a], $this->originPrices));
        $prices[] = $this->isKeep ? 1 : -1;
        return $prices;
    }
}