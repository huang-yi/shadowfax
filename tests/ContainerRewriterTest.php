<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use PHPUnit\Framework\TestCase;
use HuangYi\Shadowfax\ContainerRewriter;

class ContainerRewriterTest extends TestCase
{
    public function test_rewrite()
    {
        $rewriter = new ContainerRewriter;

        $rewriter
            ->setPath(__DIR__.'/container.php')
            ->rewrite();

        $this->assertTrue(file_exists($rewriter->getPath()));
        $this->assertStringContainsString('shadowfax_correct_app', file_get_contents($rewriter->getPath()));

        unlink($rewriter->getPath());
    }
}
