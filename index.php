<?php
/**
 * Sample usage of CandyCrusher class
 *
 * Open your browser and find your session ID
 */
require_once 'CandyCrusher.php';

$session = '';

$cc = new CandyCrusher($session, true);
for ($i = 31; $i <= 40; $i++) {
    $return = $cc->playGame($i);
    echo "Level {$i} played<br />";
}