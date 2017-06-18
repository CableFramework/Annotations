<?php

namespace Cable\Annotation;

use Cable\Annotation\Parser\AnnotationParser;
use Cable\Annotation\Parser\CommandParser;
use Cable\Annotation\Parser\Exception\ParserException;
use Cable\Annotation\Parser\ParserInterface;

/**
 * Class Parser
 * @package Cable\Annotation
 */
class Parser implements ParserInterface
{
    /**
     * @var string
     */
    private $document;


    /**
     * @var array
     */
    private $phpDoc = [
        '@return',
        '@param',
        '@var',
    ];


    /**
     * @var array
     */
    private $skip = [];

    /**
     * Parser constructor.
     * @param string $document
     */
    public function __construct(string $document = '')
    {
        $this->document = $document;
    }

    /**
     * @return string
     */
    public function getDocument(): string
    {
        return $this->document;
    }

    /**
     * @param string $document
     * @return Parser
     */
    public function setDocument(string $document): Parser
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
    private function cleanStarting(string $document): string
    {
        return str_replace(array('/**', '*/'), '', $document);
    }


    /**
     * find command and match string
     *
     * @param int $i
     * @param array $matches
     * @return array
     */
    private function findCommand(int $i, array $matches)
    {
        $match = $matches[0][$i];
        $cmd = $matches['function'][$i] ?? '';

        return [$match, $cmd];
    }

    /**
     * find string and return it
     *
     * @param int $i
     * @param array $matches
     * @return bool|string
     */
    private function findString(int $i, array $matches)
    {
        $string = $matches[0][$i] ?? '';


        return substr($string, 1, strlen($string) - 2);
    }

    /**
     * @param string $str
     * @return array
     */
    public function directParse(string $str): array
    {
        // regex
        $re = '/@(?\'function\'[\w]*)[\s\n\r]*|(?\'param\'\((?:[^\(\)]|(?R))*\))/i';

        $commands = [];


        if (preg_match_all($re, $str, $matches)) {
            $count = count($matches[0]);

            for ($i = 0; $i < $count; $i++) {
                [$match, $cmd] = $this->findCommand($i, $matches);
                ++$i;


                $parser = new AnnotationParser(
                    $match,
                    $cmd,
                    $this->findString($i, $matches)
                );

                $commands[$cmd][] = $parser->parse()->toArray();
            }
        }


        return $commands;
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
    private function cleanWildcard(string $command): string
    {
        return str_replace('*', '', $command);
    }


    /**
     * @param array $lines
     * @return array
     */
    private function filterSkip(array $lines): array
    {
        return array_filter($lines, function (string $line) {
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
    public function filterEmptyLines(array $lines): array
    {
        return array_filter($lines, function (string $line) {
            $line = trim($line);

            return $line !== '*' ? $line : false;
        });
    }


    /**
     * @return Parser
     */
    public function skipPhpDoc(): Parser
    {
        $this->skip = array_merge($this->skip, $this->phpDoc);
        return $this;
    }


    /**
     * @throws ParserException
     */
    public function checkCommentIsValid(): void
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
