<?php

declare(strict_types=1);

namespace PhpDocChecker;

/**
 * Console command to check a directory of PHP files for Docblocks.
 *
 * @package PHPDoc Checker
 *
 * @author Dmitry Khomutov <poisoncorpsee@gmail.com>
 * @author Dan Cryer <dan@block8.co.uk>
 */
class CheckerFileProcessor
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var bool
     */
    protected $skipClasses = false;

    /**
     * @var bool
     */
    protected $skipMethods = false;

    /**
     * @var bool
     */
    protected $skipSignatures = false;

    public function __construct(
        string $basePath,
        bool $skipClasses = false,
        bool $skipMethods = false,
        bool $skipSignatures = false
    ) {
        $this->basePath       = $basePath;
        $this->skipClasses    = $skipClasses;
        $this->skipMethods    = $skipMethods;
        $this->skipSignatures = $skipSignatures;
    }

    /**
     * Check a specific PHP file for errors.
     */
    public function processFile(string $file): array
    {
        $errors    = [];
        $warnings  = [];
        $processor = new FileProcessor($this->basePath . '/' . $file);

        if (!$this->skipClasses) {
            foreach ($processor->getClasses() as $name => $class) {
                if (\is_null($class['docblock'])) {
                    $errors[] = [
                        'type'  => 'class',
                        'file'  => $file,
                        'class' => $class['name'],
                        'line'  => $class['line'],
                    ];
                }
            }
        }

        if (!$this->skipMethods) {
            foreach ($processor->getMethods() as $name => $method) {
                if (\is_null($method['docblock'])) {
                    $errors[] = [
                        'type'   => 'method',
                        'file'   => $file,
                        'class'  => $method['class'],
                        'method' => $method['name'],
                        'line'   => $method['line'],
                    ];
                }
            }
        }

        if (!$this->skipSignatures) {
            foreach ($processor->getMethods() as $name => $method) {
                if (\count($method['params'])) {
                    foreach ($method['params'] as $param => $type) {
                        if (empty($method['docblock']['params'][$param])) {
                            $warnings[] = [
                                'type'   => 'param-missing',
                                'file'   => $file,
                                'class'  => $method['class'],
                                'method' => $method['name'],
                                'line'   => $method['line'],
                                'param'  => $param,
                            ];
                        } elseif (\is_array($type)) {
                            $docblockTypes     = (array)\explode('|', $method['docblock']['params'][$param]);
                            $normalizedType    = $type;

                            if (!$type && $docblockTypes) {
                                continue;
                            }

                            \sort($docblockTypes, SORT_STRING);
                            \sort($normalizedType, SORT_STRING);

                            if ($normalizedType !== $docblockTypes) {
                                $warnings[] = [
                                    'type'       => 'param-mismatch',
                                    'file'       => $file,
                                    'class'      => $method['class'],
                                    'method'     => $method['name'],
                                    'line'       => $method['line'],
                                    'param'      => $param,
                                    'param-type' => \implode('|', $type),
                                    'doc-type'   => $method['docblock']['params'][$param],
                                ];
                            }
                        } elseif (!empty($type) && $method['docblock']['params'][$param] !== $type) {
                            if (
                                ($type === 'array' && \substr($method['docblock']['params'][$param], -2) === '[]')
                                || $method['docblock']['params'][$param] === 'mixed'
                            ) {
                                // Do nothing because this is fine.
                            } else {
                                $warnings[] = [
                                    'type'       => 'param-mismatch',
                                    'file'       => $file,
                                    'class'      => $method['class'],
                                    'method'     => $method['name'],
                                    'line'       => $method['line'],
                                    'param'      => $param,
                                    'param-type' => $type,
                                    'doc-type'   => $method['docblock']['params'][$param],
                                ];
                            }
                        }
                    }
                }


                if (!empty($method['return'])) {
                    if (empty($method['docblock']['return'])) {
                        // https://bugs.php.net/bug.php?id=75263
                        if ($method['name'] === '__construct') {
                            continue;
                        }

                        $warnings[] = [
                            'type'   => 'return-missing',
                            'file'   => $file,
                            'class'  => $method['class'],
                            'method' => $method['name'],
                            'line'   => $method['line'],
                        ];
                    } elseif (\is_array($method['return'])) {
                        $docblockTypes = (array)\explode('|', $method['docblock']['return']);

                        \sort($docblockTypes, SORT_STRING);
                        \sort($method['return'], SORT_STRING);

                        if ($method['return'] !== $docblockTypes) {
                            $warnings[] = [
                                'type'        => 'return-mismatch',
                                'file'        => $file,
                                'class'       => $method['class'],
                                'method'      => $method['name'],
                                'line'        => $method['line'],
                                'return-type' => \implode('|', $method['return']),
                                'doc-type'    => $method['docblock']['return'],
                            ];
                        }
                    } elseif ($method['docblock']['return'] !== $method['return']) {
                        if (
                            ($method['return'] === 'array' && \substr($method['docblock']['return'], -2) === '[]')
                            || $method['docblock']['return'] === 'mixed'
                            || (\strpos($method['docblock']['return'], '|') !== false && PHP_MAJOR_VERSION < 8)
                        ) {
                            // Do nothing because this is fine.
                        } else {
                            $warnings[] = [
                                'type'        => 'return-mismatch',
                                'file'        => $file,
                                'class'       => $method['class'],
                                'method'      => $method['name'],
                                'line'        => $method['line'],
                                'return-type' => $method['return'],
                                'doc-type'    => $method['docblock']['return'],
                            ];
                        }
                    }
                }
            }
        }

        return [
            'errors'   => $errors,
            'warnings' => $warnings,
        ];
    }
}
