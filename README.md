<img src="https://raw.githubusercontent.com/apie-lib/apie-lib-monorepo/main/docs/apie-logo.svg" width="100px" align="left" />
<h1>serializer</h1>






 [![Latest Stable Version](https://poser.pugx.org/apie/serializer/v)](https://packagist.org/packages/apie/serializer) [![Total Downloads](https://poser.pugx.org/apie/serializer/downloads)](https://packagist.org/packages/apie/serializer) [![Latest Unstable Version](https://poser.pugx.org/apie/serializer/v/unstable)](https://packagist.org/packages/apie/serializer) [![License](https://poser.pugx.org/apie/serializer/license)](https://packagist.org/packages/apie/serializer) [![PHP Composer](https://apie-lib.github.io/projectCoverage/coverage-serializer.svg)](https://apie-lib.github.io/projectCoverage/serializer/index.html)  

[![PHP Composer](https://github.com/apie-lib/serializer/actions/workflows/php.yml/badge.svg?event=push)](https://github.com/apie-lib/serializer/actions/workflows/php.yml)

This package is part of the [Apie](https://github.com/apie-lib) library.
The code is maintained in a monorepo, so PR's need to be sent to the [monorepo](https://github.com/apie-lib/apie-lib-monorepo/pulls)

## Documentation
The Apie serializer serializes stored data to the customers or backwards. It is very similar to the Symfony serializer, except the context array in Symfony serializer is replaced with a ApieSerializerContext which also contains method for recursive calls. It still has the same logic related to decoding/encoding/normalizing and denormalizing.

### Normalization and denormalization Usage
The simples use is just calling the static method create or customize it with the constructor method:
```php
use Apie\Core\Context\ApieContext;
use Apie\Serializer\Serializer;

$serializer = Serializer::create();
// returns new SerializedHashmap(['id' => 'example@example.com', 'password' => 'p@ssW0rd'])
$serializer->normalize(new User('example@example.com', 'p@ssW0rd'), new ApieContext());
// returns new User('example.com', 'p@ssW0rd')
$serializer->denormalizeNewObject(
    ['id' => 'example@example.com', 'password' => 'p@ssW0rd'],
    User::class,
    new ApieContext()
);
```
### Customization
To add your own normalization logic, you need to add a class implementing Apie\Serializer\Interfaces\NormalizerInterface. 
To add your own denormalization logic, you need to add a class implementing Apie\Serializer\Interfaces\DenormalizerInterface.
