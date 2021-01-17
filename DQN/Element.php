<?php
namespace DQN;

use \Exception;

/**
 * 组成序列的元素
 */
class Element
{
    /** @var Sequence|null */
    private $sequence = null;

    /** @var Action|null */
    private $action = null;

    /** @var Image */
    private $image;

    /**
     * @param Sequence $st 序列，可以为null
     * @param Action $at 动作，可以为null
     * @param Image $tAdd1 图像，不能为null
     */
    public function __construct($st, $at, Image $tAdd1)
    {
        if (!is_null($st) && !is_object($st)) {
            throw new Exception('Format error, $st=' . print_r($st, true));
        }
        if (!is_null($at) && !is_object($at)) {
            throw new Exception('Format error, $at=' . print_r($at, true));
        }
        $this->sequence = $st;
        $this->action = $at;
        $this->image = $tAdd1;
    }

    /**
     *返回元素内保存的图像
     *
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }
}