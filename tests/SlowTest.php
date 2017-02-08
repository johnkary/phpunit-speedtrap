<?php declare(strict_types=1);

namespace JohnKary\PHPUnit\Tests\Listener;

use PHPUnit\Framework\TestCase;

final class SlowTest extends TestCase
{
    public function testListener()
    {
        sleep(1);
        $this->assertTrue(true);
    }
}
