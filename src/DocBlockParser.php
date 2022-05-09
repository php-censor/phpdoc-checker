<?php

declare(strict_types=1);

namespace PhpDocChecker;

/**
 * Parse the docblock of a function or method
 *
 * @package PHPDoc Checker
 *
 * @author Dmitry Khomutov <poisoncorpsee@gmail.com>
 * @author Dan Cryer <dan@block8.co.uk>
 * @author Paul Scott <paul@duedil.com> (http://www.github.com/icio/PHP-DocBlock-Parser)
 */
class DocBlockParser
{
    /**
     * Tags in the docblock that have a whitepace-delimited number of parameters
     * (such as `@param type var desc` and `@return type desc`) and the names of
     * those parameters.
     *
     * @type array
     */
    public static array $vectors = [
        'param'  => ['type', 'var', 'desc'],
        'return' => ['type', 'desc'],
    ];

    /**
     * The description of the symbol
     *
     * @type string
     */
    public string $description;

    /**
     * The tags defined in the docblock.
     *
     * The array has keys which are the tag names (excluding the @) and values
     * that are arrays, each of which is an entry for the tag.
     *
     * In the case where the tag name is defined in {@see DocBlock::$vectors} the
     * value within the tag-value array is an array in itself with keys as
     * described by {@see DocBlock::$vectors}.
     *
     * @type array
     */
    public array $tags;

    /**
     * The entire DocBlock comment that was parsed.
     *
     * @type string
     */
    public string $comment;

    /**
     * CONSTRUCTOR.
     *
     * @param string|null $comment The text of the docblock
     */
    public function __construct(?string $comment = null)
    {
        if ($comment) {
            $this->setComment($comment);
        }
    }

    /**
     * Set and parse the docblock comment.
     *
     * @param string $comment The docblock
     */
    public function setComment(string $comment): void
    {
        $this->description = '';
        $this->tags        = [];
        $this->comment     = $comment;

        $this->parseComment($comment);
    }

    /**
     * Parse the comment into the component parts and set the state of the object.
     *
     * @param string $comment The docblock
     */
    protected function parseComment(string $comment): void
    {
        // Strip the opening and closing tags of the docblock
        $comment = \substr($comment, 3, -2);

        // Split into arrays of lines
        $comment = \preg_split('/\r?\n\r?/', $comment);

        // Trim asterisks and whitespace from the beginning and whitespace from the end of lines
        $comment = \array_map(function ($line) {
            return \ltrim(\rtrim($line), "* \t\n\r\0\x0B");
        }, $comment);

        // Group the lines together by @tags
        $blocks = [];
        $b = -1;
        foreach ($comment as $line) {
            if (self::isTagged($line)) {
                $b++;
                $blocks[] = [];
            } elseif ($b == -1) {
                $b = 0;
                $blocks[] = [];
            }
            $blocks[$b][] = $line;
        }

        // Parse the blocks
        foreach ($blocks as $block => $body) {
            $body = \trim(\implode("\n", $body));

            if ($block == 0 && !self::isTagged($body)) {
                // This is the description block
                $this->description = $body;
                continue;
            } else {
                // This block is tagged
                $tag  = (string)\substr(self::strTag($body), 1);
                $body = \ltrim((string)\substr($body, \strlen($tag) + 2));

                if (isset(self::$vectors[$tag])) {
                    // The tagged block is a vector
                    $count = \count(self::$vectors[$tag]);
                    if ($body) {
                        $parts = \preg_split('/\s+/', $body, $count);
                    } else {
                        $parts = [];
                    }
                    // Default the trailing values
                    $parts  = \array_pad($parts, $count, null);
                    $mapped = \array_combine(
                        self::$vectors[$tag],
                        $parts
                    );

                    if (isset($mapped['var']) && \substr($mapped['var'], 0, 3) === '...') {
                        $mapped['var'] = substr($mapped['var'], 3);
                    }
                    // Store as a mapped array
                    $this->tags[$tag][] = $mapped;
                } else {
                    // The tagged block is only text
                    $this->tags[$tag][] = $body;
                }
            }
        }
    }

    /**
     * Whether or not a docblock contains a given @tag.
     *
     * @param string $tag The name of the @tag to check for
     */
    public function hasTag(string $tag): bool
    {
        return \is_array($this->tags) && \array_key_exists($tag, $this->tags);
    }

    /**
     * The value of a tag
     */
    public function tag(string $tag): ?array
    {
        return $this->hasTag($tag) ? $this->tags[$tag] : null;
    }

    /**
     * The value of a tag (concatenated for multiple values)
     *
     * @param string $sep The separator for concatenating
     */
    public function tagImplode(string $tag, string $sep = ' '): ?string
    {
        return $this->hasTag($tag) ? implode($sep, $this->tags[$tag]) : null;
    }

    /**
     * The value of a tag (merged recursively)
     *
     * @return array
     */
    public function tagMerge(string $tag): ?array
    {
        return $this->hasTag($tag) ? array_merge_recursive($this->tags[$tag]) : null;
    }

    /**
     * Whether or not a string begins with a @tag
     */
    public static function isTagged(string $str): bool
    {
        return isset($str[1]) && $str[0] == '@' && \ctype_alpha($str[1]);
    }

    /**
     * The tag at the beginning of a string
     */
    public static function strTag(string $str): string
    {
        if (\preg_match('/^@[a-z0-9_]+/', $str, $matches)) {
            return (string)$matches[0];
        }

        return '';
    }
}
