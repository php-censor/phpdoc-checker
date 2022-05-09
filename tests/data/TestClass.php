<?php

declare(strict_types = 1);

namespace Test\Example;

class TestClass
{
    public function test1($param1)
    {
    }

    public function test2(int $param1): bool
    {
        return false;
    }

    public function test3(?int $param1): ?bool
    {
        return false;
    }

    public function test4(int|float $param1): bool|int
    {
        return false;
    }

    /**
     * @param int $param1
     */
    public function test11($param1)
    {
    }

    /**
     * @param $param1
     */
    public function test111($param1)
    {
    }

    /**
     * @param int $param1
     *
     * @return bool
     */
    public function test12(int $param1): bool
    {
        return false;
    }

    /**
     * @param int|null $param1
     *
     * @return bool|int
     */
    public function test121(int $param1): bool
    {
        return false;
    }

    /**
     * @param int|null $param1
     *
     * @return bool|null
     */
    public function test13(?int $param1): ?bool
    {
        return false;
    }

    /**
     * @param null|int $param1
     *
     * @return null|bool
     */
    public function test131(?int $param1): ?bool
    {
        return false;
    }

    /**
     * @param int $param1
     *
     * @return bool
     */
    public function test132(?int $param1): ?bool
    {
        return false;
    }

    /**
     * @param int|float $param1
     *
     * @return bool|int
     */
    public function test14(int|float $param1): bool|int
    {
        return false;
    }

    /**
     * @param float|int $param1
     *
     * @return int|bool
     */
    public function test141(int|float $param1): bool|int
    {
        return false;
    }

    /**
     * @param int $param1
     *
     * @return bool
     */
    public function test141(int|float $param1): bool|int
    {
        return false;
    }
}
