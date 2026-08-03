<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$model = new App\Models\SuratKeputusan;
echo "method_exists: ".var_export(method_exists($model,'catatPerubahanData'),true).PHP_EOL;
$r = new ReflectionMethod($model,'catatPerubahanData');
echo "declared: ".$r->getDeclaringClass()->getName().PHP_EOL;
echo "file: ".$r->getFileName().":".$r->getStartLine().PHP_EOL;
echo "dataBaru type: ".$r->getParameters()[0]->getType().PHP_EOL;
