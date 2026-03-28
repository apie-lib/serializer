<?php
namespace Apie\Serializer\PropertySerializer;

enum TypeDefinition: string
{
    case Null = 'null';
    case String = 'string';
    case Int = 'int';
    case Float = 'float';
    case Bool = 'bool';
    case Array = 'array';
    case Map = 'map';
    case ObjectReference = 'object_reference';
}
