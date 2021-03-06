<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Tables\app\Attributes\Button;
use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class ButtonException extends EnsoException
{
    public static function wrongFormat()
    {
        return new static(__('The buttons array may contain only strings and objects'));
    }

    public static function undefined($buttons)
    {
        return new static(__(
            'Unknown Button(s) Found: ":buttons"',
            ['buttons' => $buttons]
        ));
    }

    public static function missingAttributes()
    {
        return new static(__(
            'The following attributes are mandatory for buttons: ":attrs"',
            ['attrs' => collect(Button::Mandatory)->implode('", "')]
        ));
    }

    public static function unknownAttributes()
    {
        return new static(__(
            'The following optional attributes are allowed for buttons: ":attrs"',
            ['attrs' => collect(Button::Optional)->implode('", "')]
        ));
    }

    public static function missingRoute()
    {
        return new static(__(
            'Whenever you set an action for a button you need to provide the fullRoute or routeSuffix'
        ));
    }

    public static function missingMethod()
    {
        return new static(__(
            'Whenever you set an ajax action for a button you need to provide the method aswell'
        ));
    }

    public static function wrongAction()
    {
        return new static(__(
            'The following actions are allowed for buttons: ":actions"',
            ['actions' => collect(Button::Actions)->implode('", "')]
        ));
    }

    public static function routeNotFound($route)
    {
        return new static(__(
            'Button route does not exist: ":route"',
            ['route' => $route]
        ));
    }

    public static function invalidMethod($method)
    {
        return new static(__(
            'Method is incorrect: ":method"',
            ['method' => $method]
        ));
    }
}
