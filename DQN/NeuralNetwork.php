<?php
namespace DQN;

use \Exception;

/**
 * 神经网络
 */
class NeuralNetwork
{
    /** @var resource */
    private $fannResource;

    /** @var array */
    private $neuralNetworkLayersConfig;

    /**
     * @param int[] $neuralNetworkLayersConfig 神经网络每层细胞数配置
     * @param string $loadFile 如果非空那么忽略$neuralNetworkLayersConfig，从$loadFile读取神经网络数据
     */
    public function __construct(array $neuralNetworkLayersConfig, string $loadFile = '')
    {
        if (!empty($loadFile)) {
            $this->createFromFile($loadFile);
        } else {
            $this->createFromConfig($neuralNetworkLayersConfig);
        }
    }

    /**
     * 随机设置权重
     *
     * 注意，NeuralNetwork的实现依赖于FANN扩展的fann_randomize_weights函数，这个
     * 函数在独立的FANN资源上调用时，随机数是独立生成的。也就是说两个FANN资源按照顺序
     * 调用随机化权重，那么最后的结果是这两个FANN资源的权重是一样的。
     *
     * @return void
     */
    public function randomWeights()
    {
        if (!fann_randomize_weights($this->fannResource, -0.1, 0.1)) {
            throw new Exception('fann_randomize_weights fail');
        }
    }

    /**
     * 返回神经网络所有连接的信息
     *
     * @return FANNConnection[]
     */
    public function getConnectionArray(): array
    {
        return fann_get_connection_array($this->fannResource);
    }

    /**
     * 打印权重信息，仅用于调试
     *
     * @return void
     */
    public function showWeightInfo()
    {
        echo 'WEIGHT INFO BEGIN--------------' . PHP_EOL;
        foreach (fann_get_connection_array($this->fannResource) as $k => $c) {
            echo "[$k] " . $c->getWeight() . PHP_EOL;
        }
        echo 'WEIGHT INFO END----------------' . PHP_EOL;
    }

    /**
     * 从指定的神经网络复制权重
     *
     * @param NeuralNetwork
     * @return void
     */
    public function copyWeightsFrom(NeuralNetwork $another)
    {
        fann_set_weight_array($this->fannResource, $another->getConnectionArray());
    }

    /**
     * 梯度下降
     *
     * @param array $x 输入信息，每一个元素包含KEY sequence 一个序列对象和 action 一个动作对象
     * @param array $y 目标标签信息，包含的元素是一个个浮点数，对应神经网络的输出
     * @return void
     */
    public function gradientDescent(array $x, array $y)
    {
        $numberOfData = count($x, COUNT_NORMAL);
        if ($numberOfData != count($y, COUNT_NORMAL)) {
            throw new Exception('x != y');
        }
        $target = [];
        foreach ($y as $v) {
            if (!is_numeric($v)) {
                throw new Exception('y not a number[] v=' . print_r($v, true));
            }
            $yr = $v / 30;
            if ($yr > 1) {
                $yr = 1;
            } elseif ($yr < -1) {
                $yr = -1;
            }
            $target[] = $yr;
        }
        foreach ($x as $v) {
            if (!is_array($v) || !isset($v['sequence']) || !isset($v['action']) || !is_object($v['sequence']) || !is_object($v['action'])) {
                throw new Exception('x error format');
            }
        }
        $trainData = fann_create_train_from_callback(
            $numberOfData,
            reset($this->neuralNetworkLayersConfig),
            end($this->neuralNetworkLayersConfig),
            function(int $numberOfData, int $numberOfInput, int $numberOfOutput) use ($x, $target) {
                static $n = 0;
                if (!isset($target[$n])) {
                    throw new Exception('Format error, n=' . $n . ' ' . print_r($target, true));
                }
                if (!isset($x[$n]) || !isset($x[$n]['sequence']) || !is_object($x[$n]['sequence'])) {
                    throw new Exception('Format error, n=' . $n . ' ' . print_r($x, true));
                }
                if (!isset($x[$n]) || !isset($x[$n]['action']) || !is_object($x[$n]['action'])) {
                    throw new Exception('Format error, n=' . $n . ' ' . print_r($x, true));
                }
                $input = array_merge($x[$n]['sequence']->toArray(), $x[$n]['action']->toArray());
                if (!isset($input[$numberOfInput - 1]) || isset($input[$numberOfInput])) {
                    throw new Exception('input number error, need ' . $numberOfInput . ' but:' . count($input, COUNT_NORMAL));
                }
                $result = [
                    'input' => $input,
                    'output' => [$target[$n]],
                ];
                $n++;
                return $result;
            }
        );
        if (false === $trainData) {
            throw new Exception('create data fail');
        }
        fann_train_on_data($this->fannResource, $trainData, 100, 0, 0); // 第三个参数是训练次数
        fann_destroy_train($trainData);
    }

    /**
     * 运行网络，返回一个输出值
     *
     * @param array $x 输入信息，每一个元素包含KEY sequence 一个序列对象和 action 一个动作对象
     */
    public function run($x): float
    {
        if (!isset($x['sequence']) || !is_object($x['sequence'])) {
            throw new Exception('Format error, x=' . print_r($x, true));
        }
        if (!isset($x['action']) || !is_object($x['action'])) {
            throw new Exception('Format error, x=' . print_r($x, true));
        }
        $input = array_merge($x['sequence']->toArray(), $x['action']->toArray());
        $inputNumber = reset($this->neuralNetworkLayersConfig);
        if (!isset($input[$inputNumber - 1]) || isset($input[$inputNumber])) {
            throw new Exception('NeuralNetwork input=' . $inputNumber . ', but get ' . count($input, COUNT_NORMAL));
        }
        $result = fann_run($this->fannResource, $input);
        if (false === $result) {
            throw new Exception('Fann run Fail!' . print_r($x, true));
        }
        return $result[0];
    }

    /**
     * 保存到文件
     *
     * @param string $file
     * @return void
     */
    public function save(string $file)
    {
        fann_save($this->fannResource, $file);
    }

    /**
     * 销毁神经网络
     */
    public function __destruct()
    {
        fann_destroy($this->fannResource);
    }

    /**
     * 根据一份指定了每层神经元数量的配置创建神经网络
     *
     * @param string $loadFile 神经网络数据保存文件路径
     * @return void
     */
    private function createFromFile(string $loadFile)
    {
        if (!is_file($loadFile)) {
            throw new Exception('Bad file: "' . $loadFile . '"');
        }
        $this->fannResource = fann_create_from_file($loadFile);
        if (false === $this->fannResource) {
            throw new Exception('Bad file: "' . $loadFile . '", format error');
        }
        $this->neuralNetworkLayersConfig = fann_get_layer_array($this->fannResource);
    }

    /**
     * 根据一份指定了每层神经元数量的配置创建神经网络
     *
     * @param int[] $neuralNetworkLayersConfig 神经网络每层细胞数配置
     * @return void
     */
    private function createFromConfig(array $neuralNetworkLayersConfig)
    {
        if (count($neuralNetworkLayersConfig, COUNT_NORMAL) < 2) {
            throw new Exception('Error, $neuralNetworkLayersConfig < 2, network=' . PHP_EOL . print_r($neuralNetworkLayersConfig, true));
        }
        foreach ($neuralNetworkLayersConfig as $e) {
            if (!is_int($e) || $e < 1) {
                throw new Exception('Formate error, $neuralNetworkLayersConfig < 2' . print_r($neuralNetworkLayersConfig, true));
            }
        }
        $this->neuralNetworkLayersConfig = $neuralNetworkLayersConfig;
        $this->fannResource = fann_create_standard_array(count($neuralNetworkLayersConfig, COUNT_NORMAL), $neuralNetworkLayersConfig);
        fann_set_activation_function_hidden($this->fannResource, FANN_SIGMOID_SYMMETRIC);
        fann_set_activation_function_output($this->fannResource, FANN_SIGMOID_SYMMETRIC);
        fann_set_training_algorithm($this->fannResource, FANN_TRAIN_BATCH);
        fann_set_learning_rate($this->fannResource, 0.001);
        fann_set_train_error_function($this->fannResource, FANN_ERRORFUNC_LINEAR);
        fann_set_train_stop_function($this->fannResource, FANN_STOPFUNC_MSE);
    }
}