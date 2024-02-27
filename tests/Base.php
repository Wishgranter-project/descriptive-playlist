<?php

namespace WishgranterProject\DescriptivePlaylist\Tests;

use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    protected function resetTest(string $file = null, string $template = './tests/template.dpls')
    {
        $contents = $template == '' ? '' : file_get_contents($template);
        file_put_contents($file, $contents);
    }

    protected function testExample()
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file, '');
    }
}
