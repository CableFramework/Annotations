<?php

namespace Cable\Annotation;

use Cable\Annotation\Parser\DirectParser;
use Cable\Annotation\Parser\Exception\ParserException;

/**
 * Class Parser
 * @package Cable\Annotation
 */
class Parser implements DocumentedParserInterface
{
    /**
     * @var string
     */
    private $document;


    /**
     * @var array
     */
    private static $phpDoc = [
        '@return',
        '@param',
        '@var',
        '@author',
        '@api',
        '@category',
        '@copyright',
        '@example',
        '@filesource',
        '@ignore',
        '@global',
        '@internal',
        '@license',
        '@link',
        '@method',
        '@package',
        '@property',
        '@property-read',
        '@property-write',
        '@see',
        '@since',
        '@source',
        '@subpackage',
        '@throws',
        '@todo',
        '@uses',
        '@version   '
    ];


    /**
     * @var array
     */
    private $skip = [];

    /**
     * Parser constructor.
     * @param string $document
     */
    public function __construct($document = '')
    {
        $this->document = $document;
    }

    /**
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param string $document
     * @return Parser
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }


    /**
     * search and replace comment starting and ending charecters
     *
     * @param string $document
     * @return string
     */
    private function cleanStarting($document)
    {
        return str_replace(array('/**', '*/'), '', $document);
    }



    /**
     * @param string $str
     * @return array
     */
    public function directParse($str)
    {
        return (new DirectParser())
            ->setDocument($str)
            ->parse();
    }

    /**
     *
     * @throws ParserException
     * @return mixed
     */
    public function parse()
    {
        $document = $this->document;

        // if comment not started with /** we will throw an exception
        $this->checkCommentIsValid();
        // search and replace starting and ending charecters

        $document = $this->cleanStarting($document);
        // get all lines
        $lines = explode(PHP_EOL, $document);


        if (!empty($this->skip)) {
            $lines = $this->filterSkip($lines);
        }

        // filter empty ones
        $lines = $this->filterEmptyLines($lines);


        return $this->directParse(
            $this->cleanWildcard(
                implode(PHP_EOL, $lines)
            )
        );
    }

    /**
     * @param string $command
     * @return string
     */
    private function cleanWildcard($command)
    {
        return str_replace('*', '', $command);
    }


    /**
     * @param array $lines
     * @return array
     */
    private function filterSkip(array $lines)
    {
        return array_filter($lines, function ($line) {
            foreach ($this->skip as $item) {
                if (strpos($line, $item) !== false) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * filter lines if they empty
     *
     * @param array $lines
     * @return array
     */
    public function filterEmptyLines(array $lines)
    {
        return array_filter($lines, function ($line) {
            $line = trim($line);

            return $line !== '*' ? $line : false;
        });
    }


    /**
     * @return Parser
     */
    public function skipPhpDoc()
    {
        $this->skip = array_merge($this->skip, static::$phpDoc);
        return $this;
    }


    /**
     * @throws ParserException
     */
    public function checkCommentIsValid()
    {
        if ($this->document === '') {
            return;
        }

        if (0 !== strpos($this->document, '/**')) {
            throw new ParserException(
                sprintf(
                    'Comments must start with "/**", your doc :%s',
                    $this->document
                )
            );
        }
    }
}
