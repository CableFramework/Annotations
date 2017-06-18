# Annonations
PHP Annonations library for cable framework

## Installation

```sh 

composer require cable/cable-annotation

```

```php

use Cable\Annotation\Factory;


$annotation = Factory::create();

```


## Add a Command


```php
/**
 *
 * @Name("Test") the name of command
 *
 */
class TestCommand{


     /**
      *
      * @Annotation() // you must add this 
      *                // if you want to use that property
      *
      * @Required() // if this parameter not given by user, 
      *              //will be thrown a exception
      *
      * @Default('Default value'); // default value of property
      *                             // if you set this, required exception will be never thrown
      *                             // so there is no point of using together Default and Required
      */                            
     public $name;

}


$annotation->addCommand(new TestCommand());
```


## Using Command


```php 


class Test{


      /**
       * 
       * @Test(name = "test name")
       *
       *
       */
      public function testing(){
      
      }

}

// execute the class instance
$class = $annotation->executeClass(new Test());

$methods = $annotation->methods();
// $annotation->get('methods') // same as above


// $methods->get('Test');
foreach($methods->Test() as $test){
    echo $test->name; // test name will be printed
}

```

## Using Paramaters

### Giving Arrays

```php

    /**
     *
     * @Test(datas={test: "test"})
     *
     * will be given as ["test" = "test"]
     *
     */
```


### giving annotation into data

```php

      /**
       *
       * @Test(data= @Test(name = "data"))
       *
       *
       * // you can give @Test  into data
       */
```

### giving objects into data

```php

     /**
       *
       * @Test(data= aliasname{test:"test"})
       *
       *
       * // you must save aliasname into the container
       */

```