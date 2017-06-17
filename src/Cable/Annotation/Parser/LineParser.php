<?php

namespace Cable\Annotation\Parser;


class LineParser implements ParserInterface
{

    /**
     * @var string
     */
    private $line;

    /**
     * Constructor.
     * @param string $line
     *
     *
     * @Route(
     *     url = "/"
     *     name = ""
     * )
     *
     */
    public function __construct(string $line)
    {
        $this->line = $line;

    }


    /**
     * @return mixed
     */
    public function parse() : ParsedLineBag
    {
        $cmd = $this->line;


        if (0 === preg_match("#\((.*?)\)#", $cmd)) {
            $parse = explode(' ', $cmd);
            $cmd = $parse[0];
            unset($parse[0]);

            $cmd = $this->prepareDoc($cmd, $parse);
        }



        $command = preg_replace("#\((.*?)\)#", '', $cmd);


        if (false !== preg_match("#\((.*?)\)#", $cmd, $matches)) {
            $parse = !empty($matches) ?
                (new ParameterParser($command, $matches[1]))->parse() :
                [];
        }

        return new ParsedLineBag($command, $parse);
    }

    /**
     * @param $command
     * @param array $parse
     * @return string
     */
    private function prepareDoc($command, array $parse){
        return sprintf("%s(%s)", $command, implode(',', $parse));
    }
}
