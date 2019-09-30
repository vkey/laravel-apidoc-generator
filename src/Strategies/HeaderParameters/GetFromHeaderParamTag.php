<?php

namespace Mpociot\ApiDoc\Strategies\HeaderParameters;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Routing\Route;
use Mpociot\Reflection\DocBlock;
use Mpociot\Reflection\DocBlock\Tag;
use Mpociot\ApiDoc\Strategies\Strategy;
use Mpociot\ApiDoc\Tools\RouteDocBlocker;
use Dingo\Api\Http\FormRequest as DingoFormRequest;
use Mpociot\ApiDoc\Tools\Traits\DocBlockParamHelpers;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

class GetFromHeaderParamTag extends Strategy
{
    use DocBlockParamHelpers;

    public function __invoke(Route $route, ReflectionClass $controller, ReflectionMethod $method, array $routeRules, array $context = [])
    {
        foreach ($method->getParameters() as $param) {
            $paramType = $param->getType();
            if ($paramType === null) {
                continue;
            }

            $parameterClassName = version_compare(phpversion(), '7.1.0', '<')
                ? $paramType->__toString()
                : $paramType->getName();

            try {
                $parameterClass = new ReflectionClass($parameterClassName);
            } catch (\ReflectionException $e) {
                continue;
            }

            // If there's a FormRequest, we check there for @headerParam tags.
            if (class_exists(LaravelFormRequest::class) && $parameterClass->isSubclassOf(LaravelFormRequest::class)
                || class_exists(DingoFormRequest::class) && $parameterClass->isSubclassOf(DingoFormRequest::class)) {
                $formRequestDocBlock = new DocBlock($parameterClass->getDocComment());
                $bodyParametersFromDocBlock = $this->getHeaderParametersFromDocBlock($formRequestDocBlock->getTags());

                if (count($bodyParametersFromDocBlock)) {
                    return $bodyParametersFromDocBlock;
                }
            }
        }

        $methodDocBlock = RouteDocBlocker::getDocBlocksFromRoute($route)['method'];

        return $this->getHeaderParametersFromDocBlock($methodDocBlock->getTags());
    }

    private function getHeaderParametersFromDocBlock($tags)
    {
        $parameters = collect($tags)
            ->filter(function ($tag) {
                return $tag instanceof Tag && $tag->getName() === 'headerParam';
            })
            ->mapWithKeys(function ($tag) {
                    list($name, $type) = preg_split('/\s+/', $tag->getContent());
                return [$name => $type];
            })->toArray();

        return $parameters;
    }
}
