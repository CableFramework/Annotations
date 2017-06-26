<?php

namespace Cable\Annotation;


use Cable\Annotation\Parser\ParserInterface;

interface DocumentedParserInterface extends ParserInterface
{

    /**
     * @param string $document
     * @return mixed
     */
    public function setDocument($document);

}
