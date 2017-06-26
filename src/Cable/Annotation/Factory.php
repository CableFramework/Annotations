<?php
namespace Cable\Annotation;


/**
 * Class Factory
 * @package Cable\Annotation
 */
class Factory
{

    /**
     * @param Parser|null $parser
     * @return Annotation
     */
    public static function create(Parser $parser = null)
    {
        if (null === $parser) {
            $parser = new Parser();
        }


        return new Annotation($parser);
    }

}
