<?php
namespace DQN;

use \Exception;

/**
 * 模拟器
 */
class Emulator
{
    /**
     * @var int 正常状态，未持有状态
     */
    const S_NORMAL = 1;

    /**
     * @var int 持有
     */
    const S_KEEP = 2;

    /**
     * @var bool 错误动作
     */
    private $errorAction = false;

    /**
     * @var float 用于迭代计算，最后一次价格
     */
    private $lastPrice;

    /**
     * @var float[] 缓存的当前图像
     */
    private $cache = [];

    /**
     * @var int 默认大小
     */
    private $size;

    /**
     * @var int 状态
     */
    private $state;

    /**
     * @var float 可用资产总价格
     */
    private $price = 1000;

    /**
     * @var float
     */
    private $buyPrice = 0;

    /**
     * @var float|null
     */
    private $sellPrice = null;

    /** @var int */
    private $stepNumber = 1;

    /**
     * 设置模拟器
     *
     * @param int $size 游戏显示尺寸，默认400
     */
    public function __construct(int $size = 400)
    {
        if ($size < 2) {
            throw new Exception('size must > 1, your size=' . $size);
        }
        $this->size = $size;
        $this->reset();
    }

    //private $debug; // TODO 这个只是用于调试而已，后面去掉

    /**
     * 执行动作
     *
     * @param Action $a
     * @return void
     */
    public function execute(Action $a)
    {
        // TODO 这个只是用于调试而已，后面去掉 ***************
        /*if ($a->getActionType() == Action::AC_BUY) {
            $this->debug = true;
        } else {
            $this->debug = false;
        }
        return;*/ // TODO 这个只是用于调试而已，后面去掉 **********

        $this->cleanReword();
        switch ($this->state) {
            case self::S_NORMAL:
                if ($a->getActionType() == Action::AC_BUY || $a->getActionType() == Action::AC_SELL) {
                    $this->buyPrice = $this->lastPrice;
                    $this->price -= $this->lastPrice;
                    $this->price = round($this->price, 2);
                    $this->state = self::S_KEEP;
                }
                //if ($a->getActionType() == Action::AC_SELL) {
                //    $this->errorAction = true;
                //}
                break;
            case self::S_KEEP:
                if ($a->getActionType() == Action::AC_SELL || $a->getActionType() == Action::AC_BUY) {
                    $this->sellPrice = $this->lastPrice;
                    $this->price += $this->lastPrice;
                    $this->price = round($this->price, 2);
                    $this->state = self::S_NORMAL;
                }
                //if ($a->getActionType() == Action::AC_BUY) {
                //    $this->errorAction = true;
                //}
                break;
            default:
                throw new Exception('Your action is error!');
        }
        // 执行动作完成后，需要前进一帧
        $this->lastPrice += 0;
        //$this->lastPrice = sin($this->stepNumber / 10);
        //$this->stepNumber++;
        $this->lastPrice = round($this->lastPrice, 2);
        $this->cache[] = $this->lastPrice;
        array_shift($this->cache);
    }

    /**
     * 返回奖励
     *
     * @return float
     */
    public function getReward(): float
    {
        //return $this->debug ? 1 : -1; // TODO 这个只是用于调试而已，后面去掉
        //if ($this->errorAction) {
        //    return -100;
        //}
        if (!is_null($this->sellPrice)) {
            return round($this->sellPrice - $this->buyPrice, 2);
        }
        return 0;
    }

    /**
     * 返回当前的cache的图像
     *
     * @return Image
     */
    public function getImage(): Image
    {
        return new Image($this->cache, $this->state == self::S_KEEP, $this->buyPrice);
    }

    /**
     * 重置游戏
     *
     * @return void
     */
    public function reset()
    {
        $t = <<<JSON_DATA
{"price":["175.41","174.58","172.96","172.99","175.36","176.39","177.36","176.33","178.50","178.73","177.66","177.27","176.80","175.31","176.06","176.70","176.40","177.14","175.67","174.42","173.89","173.15","172.96","172.97","171.54","171.46","172.05","172.24","171.15","170.62","169.85","170.05","170.13","166.97","166.43","166.98","168.02","167.66","167.62","165.67","165.79","164.87","164.10","164.56","165.21","164.92","165.58","165.10","164.82","166.05","166.28","165.51","165.46","165.35","165.58","166.88","167.57","167.58","167.22","167.80","166.26","165.38","165.48","166.36","166.30","165.68","165.81","164.96","164.45","162.85","162.61","163.12","164.34","164.51","164.53","165.66","163.13","162.87","160.74","161.37","160.69","161.82","162.79","163.40","162.72","162.08","162.99","163.15","162.83","162.20","162.22","163.98","162.37","162.78","162.05","162.56","162.84","163.58","164.64","163.46","162.65","163.13","161.76","161.84","162.55","161.80","163.54","162.02","162.74","160.45","160.53","162.10","161.34","159.95","160.08","158.51","159.77","159.74","158.73","158.10","159.14","156.50","156.70","156.39","155.98","156.07","156.47","157.64","156.89","157.76","159.03","159.40","161.80","162.30","163.38","165.05","164.22","165.05","166.46","166.49","165.62","165.41","165.90","166.62","168.11","168.94","168.28","166.40","166.48","166.29","165.89","165.82","165.83","166.57","166.02","166.90","165.89","166.67","166.55","167.12","168.20","167.70","168.53","168.52","168.32","167.93","167.05","167.80","167.76","168.04","167.47","166.71","166.10","164.57","166.36","166.17","165.37","164.73","165.77","165.20","166.32","166.40","165.00","163.72","164.39","164.00","162.08","161.59","161.45","160.99","159.66","160.77","162.68","161.94","162.24","161.90","160.70","160.08","160.87","160.71","160.99","161.23","160.24","160.85","159.75","159.72","159.28","159.13","157.65","158.16","157.95","157.67","158.26","159.56","159.66","160.35","159.48","157.67","157.38","157.16","158.03","159.69","160.33","160.47","161.06","161.14","162.51","161.24","161.11","161.42","160.60","160.22","160.10","159.39","160.95","160.32","160.59","158.89","158.58","158.60","157.01","157.55","159.18","158.90","158.12","157.10","157.64","157.34","156.41","154.34","155.98","154.25","153.54","155.11","154.35","153.34","153.08","153.86","152.49","152.70","151.74","150.87","148.90","147.93","148.55","148.64","149.82","150.55","149.96","150.82","151.59","151.61","152.50","152.35","153.03","152.25","153.42","153.23","153.24","152.24","151.24","149.17","147.83","146.91","146.31","145.92","146.24","146.50","147.67","148.42","147.69","148.35","147.91","148.90","148.86","150.03","149.82","150.46","151.46","150.98","149.38","148.75","149.59","149.45","149.60","149.82","148.09","147.58","147.13","147.20","147.63","147.27","147.91","147.50","147.92","146.91","147.55","149.59","150.46","149.93","148.61","149.83","149.15","147.49","146.58","146.85","147.11","146.73","147.78","148.10","146.10","147.83","148.39","148.30","148.86","149.22","148.36","149.82","149.13","148.91","148.35","148.23","147.80","145.83","147.22","146.98","148.71","150.67","151.57","151.85","150.81","150.78","151.37","151.72","150.59","151.23","151.14","151.67","151.19","151.09","150.79","150.36","152.28","151.94","150.59","149.90","149.04","147.56","145.53","145.64","144.71","144.24","146.73","147.73","148.79","148.30","148.88","149.27","147.49","147.68","149.51","147.84","147.82","147.13","146.34","146.03","145.60","144.53","143.73","143.99","143.19","143.53","145.22","144.36","144.33","143.92","143.14","143.54","144.27","144.73"],"isKeep":false,"buyPrice":0}
JSON_DATA;
        $params = json_decode(file_get_contents('php://input'), true);
        //$params = json_decode($t, true);
        $this->cache = $params['price'];
        $this->lastPrice = end($params['price']);
        $this->state = $params['isKeep'] ? self::S_KEEP : self::S_NORMAL;
        $this->buyPrice = $params['buyPrice'];
        $this->sellPrice = null;
        $this->errorAction = false;
    }

    /**
     * 为了调试，返回游戏状态说明文字
     *
     * @return string
     */
    public function getGameStats(): string
    {
        if (self::S_NORMAL == $this->state) {
            return 'EMPT';
        }
        return 'KEEP';
    }

    /**
     * 清除暂时保存的奖励
     *
     * @return void
     */
    private function cleanReword()
    {
        // 购买价格不可以清除
        $this->sellPrice = null;
        $this->errorAction = false;
    }
}