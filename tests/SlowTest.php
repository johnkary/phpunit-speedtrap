<?php

class SlowTest extends \PHPUnit_Framework_TestCase
{
    public function testListener()
    {
        sleep(1);
    }
}
