<?php
/**
 * index.php file
 */
require_once 'CandyCrusher.php';

$session = '7B2tdivE-FV4EvLaevDQgg.1';

$cc = new CandyCrusher($session, true);
for ($i = 31; $i <= 40; $i++) {
    $return = $cc->playGame($i);
    echo "Level {$i} played<br />";
}