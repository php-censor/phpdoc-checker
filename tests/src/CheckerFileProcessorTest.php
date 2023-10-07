<?php

declare(strict_types=1);

namespace Tests\PhpDocChecker;

use PhpDocChecker\CheckerFileProcessor;
use PHPUnit\Framework\TestCase;

class CheckerFileProcessorTest extends TestCase
{
    public function testProcessFile()
    {
        $processor = new CheckerFileProcessor(
            \dirname(__DIR__) . '/data'
        );

        $result = $processor->processFile('TestClass.php');

        if (PHP_MAJOR_VERSION < 8) {
            $expectedErrors = [
                'errors' => [
                    [
                        'type'  => 'class',
                        'file'  => 'TestClass.php',
                        'class' => 'Test\Example\TestClass',
                        'line'  => 7,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test1',
                        'line'   => 9,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test2',
                        'line'   => 13,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test3',
                        'line'   => 18,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test4',
                        'line'   => 23,
                    ],
                ],
                'warnings' => [
                    [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test1',
                        'line'   => 9,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test2',
                        'line'   => 13,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test2',
                        'line'   => 13,
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test3',
                        'line'   => 18,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test3',
                        'line'   => 18,
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test4',
                        'line'   => 23,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test4',
                        'line'   => 23,
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test111',
                        'line'   => 38,
                        'param'  => '$param1',
                    ], [
                        'type'       => 'param-mismatch',
                        'file'       => 'TestClass.php',
                        'class'      => 'Test\Example\TestClass',
                        'method'     => 'test121',
                        'line'       => 57,
                        'param'      => '$param1',
                        'param-type' => 'int',
                        'doc-type'   => 'int|null',
                    ],                 [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test124',
                        'line'   => 85,
                    ], [
                        'type'       => 'param-mismatch',
                        'file'       => 'TestClass.php',
                        'class'      => 'Test\Example\TestClass',
                        'method'     => 'test132',
                        'line'       => 114,
                        'param'      => '$param1',
                        'param-type' => 'int|null',
                        'doc-type'   => 'int',
                    ], [
                        'type'        => 'return-mismatch',
                        'file'        => 'TestClass.php',
                        'class'       => 'Test\Example\TestClass',
                        'method'      => 'test132',
                        'line'        => 114,
                        'return-type' => 'bool|null',
                        'doc-type'    => 'bool',
                    ], [
                        'type'        => 'param-mismatch',
                        'file'        => 'TestClass.php',
                        'class'       => 'Test\Example\TestClass',
                        'method'      => 'test141',
                        'line'        => 144,
                        'param'      => '$param1',
                        'param-type' => 'int|float',
                        'doc-type'   => 'int',
                    ], [
                        'type'        => 'return-mismatch',
                        'file'        => 'TestClass.php',
                        'class'       => 'Test\Example\TestClass',
                        'method'      => 'test141',
                        'line'        => 144,
                        'return-type' => 'bool|int',
                        'doc-type'    => 'bool',
                    ]
                ],
            ];
        } else {
            $expectedErrors = [
                'errors' => [
                    [
                        'type'  => 'class',
                        'file'  => 'TestClass.php',
                        'class' => 'Test\Example\TestClass',
                        'line'  => 7,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test1',
                        'line'   => 9,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test2',
                        'line'   => 13,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test3',
                        'line'   => 18,
                    ], [
                        'type'   => 'method',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test4',
                        'line'   => 23,
                    ],
                ],
                'warnings' => [
                    [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test1',
                        'line'   => 9,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test2',
                        'line'   => 13,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test2',
                        'line'   => 13,
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test3',
                        'line'   => 18,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test3',
                        'line'   => 18,
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test4',
                        'line'   => 23,
                        'param'  => '$param1',
                    ], [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test4',
                        'line'   => 23,
                    ], [
                        'type'   => 'param-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test111',
                        'line'   => 38,
                        'param'  => '$param1',
                    ], [
                        'type'       => 'param-mismatch',
                        'file'       => 'TestClass.php',
                        'class'      => 'Test\Example\TestClass',
                        'method'     => 'test121',
                        'line'       => 57,
                        'param'      => '$param1',
                        'param-type' => 'int',
                        'doc-type'   => 'int|null',
                    ], [
                        'type'        => 'return-mismatch',
                        'file'        => 'TestClass.php',
                        'class'       => 'Test\Example\TestClass',
                        'method'      => 'test121',
                        'line'        => 57,
                        'doc-type'    => 'bool|int',
                        'return-type' => 'bool'
                    ],                 [
                        'type'   => 'return-missing',
                        'file'   => 'TestClass.php',
                        'class'  => 'Test\Example\TestClass',
                        'method' => 'test124',
                        'line'   => 85,
                    ], [
                        'type'       => 'param-mismatch',
                        'file'       => 'TestClass.php',
                        'class'      => 'Test\Example\TestClass',
                        'method'     => 'test132',
                        'line'       => 114,
                        'param'      => '$param1',
                        'param-type' => 'int|null',
                        'doc-type'   => 'int',
                    ], [
                        'type'        => 'return-mismatch',
                        'file'        => 'TestClass.php',
                        'class'       => 'Test\Example\TestClass',
                        'method'      => 'test132',
                        'line'        => 114,
                        'return-type' => 'bool|null',
                        'doc-type'    => 'bool',
                    ], [
                        'type'        => 'param-mismatch',
                        'file'        => 'TestClass.php',
                        'class'       => 'Test\Example\TestClass',
                        'method'      => 'test141',
                        'line'        => 144,
                        'param'      => '$param1',
                        'param-type' => 'int|float',
                        'doc-type'   => 'int',
                    ], [
                        'type'        => 'return-mismatch',
                        'file'        => 'TestClass.php',
                        'class'       => 'Test\Example\TestClass',
                        'method'      => 'test141',
                        'line'        => 144,
                        'return-type' => 'bool|int',
                        'doc-type'    => 'bool',
                    ]
                ],
            ];
        }

        self::assertEquals($expectedErrors, $result);
    }
}
