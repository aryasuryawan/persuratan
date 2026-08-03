<?php
$file = 'esuratdompd_esurat2_03082026.sql';
$sql = file_get_contents($file);
preg_match_all('/CREATE TABLE `([^`]+)` \((.*?)\) ENGINE/s', $sql, $m, PREG_SET_ORDER);
$out = [];
foreach ($m as $t) {
    $cols = [];
    preg_match_all('/^\s*`([^`]+)`\s/sm', $t[2], $cm);
    foreach ($cm[1] as $c) {
        if (!in_array($c, ['KEY', 'PRIMARY', 'UNIQUE', 'CONSTRAINT'])) $cols[] = $c;
    }
    preg_match_all('/^\s*(?:UNIQUE )?KEY `[^`]+` \([^)]+\)/sm', $t[2], $km);
    preg_match_all('/^\s*CONSTRAINT `[^`]+` FOREIGN KEY \([^)]+\)/sm', $t[2], $fm);
    $out[] = "### " . $t[1] . "\nCOLS: " . implode(', ', $cols) . "\n" . implode("\n", $km[0]) . "\n" . implode("\n", $fm[0]);
}
echo implode("\n\n", $out) . "\n";
