<?php
$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__);
    
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PHP84Migration' => true,
    ])
    ->setFinder($finder);