<?php
namespace DQN;

use \Exception;

/**
 * 动作
 */
class Action
{
    /**
     * @var int 购买动作
     */
    const AC_BUY = 1;

    /**
     * @var int 卖出动作
     */
    const AC_SELL = 2;

    /**
     * @var int 什么都不做
     */
    const AC_NOTHING = 3;

    /** @var int[] 动作类型 */
    private static $actionTypes = [self::AC_BUY, self::AC_SELL, self::AC_NOTHING];

    /** @var int 当前动作 */
    private $action;

    /**
     * 新建动作对象
     *
     * @param int $acConstValue AC_* 动作
     */
    public function __construct(int $acConstValue)
    {
        if (!in_array($acConstValue, static::$actionTypes)) {
            throw new Exception('Your action-value is not in action-list:' . $acConstValue);
        }
        $this->action = $acConstValue;
    }

    /**
     * 随机地选择一个动作
     *
     * @return Action
     */
    public static function selectRandomAction(): Action
    {
        return new static(static::$actionTypes[array_rand(static::$actionTypes)]);
    }

    /**
     * 返回动作类型
     *
     * @return int
     */
    public function getActionType(): int
    {
        return $this->action;
    }

    /**
     * 转换为元素值在-1和1之间的数组
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach (static::$actionTypes as $t) {
            $result[] = $t == $this->action ? 1 : -1;
        }
        return $result;
    }
}