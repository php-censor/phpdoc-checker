<?php

declare(strict_types = 1);

namespace PhpDocChecker;

use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UnionType;
use PhpParser\ParserFactory;

/**
 * Uses Nikic/PhpParser to parse PHP files and find relevant information for the checker.
 *
 * @package PHPDoc Checker
 *
 * @author Dmitry Khomutov <poisoncorpsee@gmail.com>
 * @author Dan Cryer <dan@block8.co.uk>
 */
class FileProcessor
{
    protected $file;
    protected $classes = [];
    protected $methods = [];

    /**
     * Load and parse a PHP file.
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;

        try {
            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
            $stmts = $parser->parse(file_get_contents($file));
            $this->processStatements($stmts);
        } catch (\Exception $ex) {}
    }

    /**
     * Return a list of class details from the given PHP file.
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Return a list of method details from the given PHP file.
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Looks for class definitions, and then within them method definitions, docblocks, etc.
     *
     * @param array  $statements
     * @param string $prefix
     *
     * @return mixed
     */
    protected function processStatements(array $statements, string $prefix = '')
    {
        $uses = [];
        foreach ($statements as $statement) {
            if ($statement instanceof Namespace_) {
                return $this->processStatements($statement->stmts, (string)$statement->name);
            }

            if ($statement instanceof Use_) {
                foreach ($statement->uses as $use) {
                    $uses[(string)$use->alias] = (string)$use->name;
                }
            }

            if ($statement instanceof Class_) {
                $class = $statement;
                $fullClassName = $prefix . '\\' . (string)$class->name;

                $this->classes[$fullClassName] = [
                    'file' => $this->file,
                    'line' => $class->getAttribute('startLine'),
                    'name' => $fullClassName,
                    'docblock' => $this->getDocblock($class, $uses),
                ];

                foreach ($statement->stmts as $method) {
                    if (!($method instanceof ClassMethod)) {
                        continue;
                    }

                    $fullMethodName = $fullClassName . '::' . $method->name;

                    $returnType = $method->returnType;
                    if ($method->returnType instanceof NullableType) {
                        $returnType = [$returnType->type, 'null'];
                    } elseif ($method->returnType instanceof UnionType) {
                        $returnType = $returnType->types;
                    } else {
                        if (!\is_null($returnType)) {
                            $returnType = (string)$returnType;
                        }
                    }

                    $returnType = (array)$returnType;
                    foreach ($returnType as &$returnTypeItem) {
                        if (isset($uses[(string)$returnTypeItem])) {
                            $returnTypeItem = $uses[(string)$returnTypeItem];
                        }

                        $returnTypeItem = \substr((string)$returnTypeItem, 0, 1) === '\\'
                            ? \substr((string)$returnTypeItem, 1)
                            : $returnTypeItem;

                        $returnTypeItem = (null !== $returnTypeItem)
                            ? (string)$returnTypeItem
                            : null;
                    }
                    unset($returnTypeItem);

                    if (1 === \count($returnType)) {
                        $returnType = (string)$returnType[0];
                    }

                    $thisMethod = [
                        'file'     => $this->file,
                        'class'    => $fullClassName,
                        'name'     => (string)$method->name,
                        'line'     => $method->getAttribute('startLine'),
                        'return'   => $returnType,
                        'params'   => [],
                        'docblock' => $this->getDocblock($method, $uses),
                    ];

                    foreach ($method->params as $param) {
                        $paramType = $param->type;
                        if ($param->type instanceof NullableType) {
                            $paramType = [$paramType->type, 'null'];
                        } elseif (
                            !empty($param->default->name->parts[0]) &&
                            'null' === $param->default->name->parts[0]
                        ) {
                            if (!\is_null($param->type)) {
                                $paramType = [$paramType, 'null'];
                            } else {
                                $paramType = ['<any>', 'null'];
                            }
                        } elseif ($param->type instanceof UnionType) {
                            $paramType = $paramType->types;
                        } else {
                            if (!\is_null($paramType)) {
                                $paramType = (string)$paramType;
                            }
                        }

                        $paramType = (array)$paramType;
                        foreach ($paramType as &$paramTypeItem) {
                            if (isset($uses[(string)$paramTypeItem])) {
                                $paramTypeItem = $uses[(string)$paramTypeItem];
                            }

                            $paramTypeItem = \substr((string)$paramTypeItem, 0, 1) === '\\'
                                ? \substr((string)$paramTypeItem, 1)
                                : $paramTypeItem;

                            $paramTypeItem = (null !== $paramTypeItem)
                                ? (string)$paramTypeItem
                                : null;
                        }
                        unset($paramTypeItem);

                        $thisMethod['params']['$'.$param->var->name] = $paramType;
                    }

                    $this->methods[$fullMethodName] = $thisMethod;
                }
            }
        }

        return;
    }

    /**
     * Find and parse a docblock for a given class or method.
     *
     * @param Stmt  $stmt
     * @param array $uses
     *
     * @return array|null
     */
    protected function getDocblock(Stmt $stmt, array $uses = []): ?array
    {
        $comments = $stmt->getAttribute('comments');

        if (\is_array($comments)) {
            foreach ($comments as $comment) {
                if ($comment instanceof Doc) {
                    return $this->processDocblock($comment->getText(), $uses);
                }
            }
        }

        return null;
    }

    /**
     * Use Paul Scott's docblock parser to parse a docblock, then return the relevant parts.
     *
     * @param string $text
     * @param array  $uses
     *
     * @return array
     */
    protected function processDocblock(string $text, array $uses = []): array
    {
        $parser = new DocBlockParser($text);

        $rtn = ['params' => [], 'return' => null];

        if (isset($parser->tags['param'])) {
            foreach ($parser->tags['param'] as $param) {
                $type = $param['type'];

                if (!is_null($type)) {
                    $type = (string)$type;
                }

                $types = [];
                foreach (\explode('|', $type) as $tmpType) {
                    if (isset($uses[$tmpType])) {
                        $tmpType = $uses[$tmpType];
                    }
                    $types[] = \substr($tmpType, 0, 1) === '\\' ? \substr($tmpType, 1) : $tmpType;
                }
                $rtn['params'][$param['var']] = \implode('|', $types);
            }
        }

        if (isset($parser->tags['return'])) {
            $return = \array_shift($parser->tags['return']);

            $type = $return['type'];

            if (!\is_null($type)) {
                $type = (string)$type;
            }

            $types = [];
            foreach (\explode('|', $type) as $tmpType) {
                if (isset($uses[$tmpType])) {
                    $tmpType = $uses[$tmpType];
                }
                $types[] = \substr($tmpType, 0, 1) === '\\' ? \substr($tmpType, 1) : $tmpType;
            }
            $rtn['return'] = \implode('|', $types);
        }

        return $rtn;
    }
}
