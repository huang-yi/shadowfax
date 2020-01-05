<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use PHPUnit\Framework\TestCase;
use HuangYi\Shadowfax\Laravel\ContainerRewriter;

class ContainerRewriterTest extends TestCase
{
    public function test_rewrite()
    {
        $rewriter = new ContainerRewriter;

        $rewriter
            ->setPath($path = __DIR__.'/container.php')
            ->rewrite();

        $this->assertStringContainsString('shadowfax_correct_app', file_get_contents($path));

        unlink($path);
    }
}
