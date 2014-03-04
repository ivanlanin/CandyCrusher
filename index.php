<?php
/**
 * Sample usage of CandyCrusher class
 *
 * Read Nick Stallman's article (URL on README.md) on how to find session ID
 */
require_once 'CandyCrusher.php';

$session = '';

$cc = new CandyCrusher($session);
for ($i = 1; $i <= 5; $i++) {
    $cc->playGame($i);
}