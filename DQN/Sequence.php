<?php
namespace DQN;

/**
 * 序列
 */
class Sequence
{
    /** @var Element */
    private $element;

    /**
     * @param Element $element
     */
    public function __construct(Element $element)
    {
        $this->element = $element;
    }

    /**
     * 转换为元素值在-1和1之间的数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->element->getImage()->toArray();
    }
}