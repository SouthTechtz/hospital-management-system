<?php

// Temporary helper: collects every translation key referenced in app/, routes/ and resources/views/.
$targets = [__DIR__.'/../app', __DIR__.'/../routes', __DIR__.'/../resources/views'];
$pat = '/__\([\'"]([a-z0-9_.]+)[\'"]/';
$keys = [];

foreach ($targets as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (! $f->isFile() || $f->getExtension() !== 'php') {
            continue;
        }
        $c = file_get_contents($f->getPathname());
        if ($c === false) {
            continue;
        }
        preg_match_all($pat, $c, $m);
        foreach ($m[1] as $k) {
            $keys[$k] = true;
        }
    }
}

ksort($keys);
echo implode(PHP_EOL, array_keys($keys)), PHP_EOL;