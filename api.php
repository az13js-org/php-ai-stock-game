<?php
header('Content-Type: application/json');

require_once 'autoload.php';

function preprocess(DQN\Sequence $st): DQN\Sequence
{
    return $st;
}

function argmax(DQN\Sequence $input, DQN\NeuralNetwork $Q): array
{
    $arr1 = $input->toArray();
    $action = new DQN\Action(DQN\Action::AC_NOTHING);
    $a = [
        new DQN\Action(DQN\Action::AC_BUY),
        new DQN\Action(DQN\Action::AC_SELL),
        $action,
    ];
    $yMin = -100;
    $bsk = [];
    foreach ($a as $ae) {
        $y = $Q->run(['sequence' => $input, 'action' => $ae]);
        if ($y > $yMin) {
            $yMin = $y;
            $action = $ae;
        }
        $bsk[] = $y;
    }
    return ['a' => $action, 'bsk' => $bsk];
}

$emulator = new DQN\Emulator();
$s1 = $emulator->getGameStats();
$Q = new DQN\NeuralNetwork([], 'data' . DIRECTORY_SEPARATOR . '20210126_last.dat');

$x = $emulator->getImage();
$s = new DQN\Sequence(new DQN\Element(null, null, $x));
$omiga = preprocess($s);
$a = argmax($omiga, $Q);
$r = [
    'buy' => $a['bsk'][0],
    'sell' => $a['bsk'][1],
    'keep' => $a['bsk'][2],
];
echo json_encode($r);
