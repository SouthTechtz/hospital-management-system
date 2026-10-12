<?php

// Compares keys used in app/, routes/, resources/views/ against lang/en/ui.php and lang/sw/ui.php.
$used = [];
$pat = '/__\([\'"]([a-z0-9_.]+)[\'"]/';
$targets = [__DIR__.'/../app', __DIR__.'/../routes', __DIR__.'/../resources/views'];

foreach ($targets as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (! $f->isFile() || $f->getExtension() !== 'php') {
            continue;
        }
        $c = (string) file_get_contents($f->getPathname());
        preg_match_all($pat, $c, $m);
        foreach ($m[1] as $k) {
            $used[$k] = true;
        }
    }
}

foreach (['en' => 'ui', 'sw' => 'ui'] as $locale => $file) {
    $lang = require __DIR__.'/../lang/'.$locale.'/'.$file.'.php';
    $missing = [];
    foreach (array_keys($used) as $key) {
        $parts = explode('.', $key);
        $node = $lang;
        foreach ($parts as $part) {
            if (! is_array($node) || ! array_key_exists($part, $node)) {
                $node = null;
                break;
            }
            $node = $node[$part];
        }
        if ($node === null) {
            $missing[] = $key;
        }
    }
    echo "=== missing in $locale ===\n";
    echo implode("\n", $missing), "\n";
    echo "\n";
}