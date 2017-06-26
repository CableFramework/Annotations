<?php

namespace Cable\Annotation\Parser;


use Cable\Annotation\DocumentedParserInterface;

class DirectParser implements DocumentedParserInterface
{

    /**
     * @var string
     */
    private $document;

    /**
     * @var string
     */
    private $regex = "/@(?'function'[\w]*)[\s\n\r]*|(?'param'\((?:[^\(\)]|(?R))*\))/i";

    /**
     * @param string $document
     * @return DirectParser
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return array
     */
    public function parse()
    {
        $commands = [];


        if (preg_match_all( $this->regex, $this->document, $matches)) {
            $count = count($matches[0]);

            for ($i = 0; $i < $count; $i++) {
                list($match, $cmd) = $this->findCommand($i, $matches);
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
     * find command and match string
     *
     * @param int $i
     * @param array $matches
     * @return array
     */
    private function findCommand($i, array $matches)
    {
        $match = $matches[0][$i];
        $cmd = isset($matches['function'][$i]) ? $matches['function'][$i] : '';

        return [$match, $cmd];
    }

    /**
     * find string and return it
     *
     * @param int $i
     * @param array $matches
     * @return bool|string
     */
    private function findString($i, array $matches)
    {
        $string = isset($matches[0][$i]) ? $matches[0][$i] : '';


        return substr($string, 1, -1);
    }

}