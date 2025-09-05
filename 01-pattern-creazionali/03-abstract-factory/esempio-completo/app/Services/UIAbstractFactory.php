<?php

namespace App\Services;

use App\Models\UIComponent;

interface UIAbstractFactoryInterface
{
    public function createButton(string $content): UIComponent;
    public function createCard(string $content): UIComponent;
    public function createModal(string $content): UIComponent;
    public function getThemeName(): string;
}

class DarkThemeFactory implements UIAbstractFactoryInterface
{
    public function createButton(string $content): UIComponent
    {
        return new UIComponent(
            type: 'button',
            theme: 'dark',
            styles: [
                'background-color' => '#2d3748',
                'color' => '#ffffff',
                'border' => '1px solid #4a5568',
                'padding' => '8px 16px',
                'border-radius' => '4px',
                'cursor' => 'pointer'
            ],
            content: $content,
            attributes: ['class' => 'btn-dark']
        );
    }

    public function createCard(string $content): UIComponent
    {
        return new UIComponent(
            type: 'div',
            theme: 'dark',
            styles: [
                'background-color' => '#1a202c',
                'color' => '#e2e8f0',
                'border' => '1px solid #2d3748',
                'padding' => '16px',
                'border-radius' => '8px',
                'box-shadow' => '0 4px 6px rgba(0, 0, 0, 0.3)'
            ],
            content: $content,
            attributes: ['class' => 'card-dark']
        );
    }

    public function createModal(string $content): UIComponent
    {
        return new UIComponent(
            type: 'div',
            theme: 'dark',
            styles: [
                'background-color' => '#2d3748',
                'color' => '#ffffff',
                'border' => '2px solid #4a5568',
                'padding' => '24px',
                'border-radius' => '12px',
                'box-shadow' => '0 10px 25px rgba(0, 0, 0, 0.5)',
                'position' => 'fixed',
                'top' => '50%',
                'left' => '50%',
                'transform' => 'translate(-50%, -50%)'
            ],
            content: $content,
            attributes: ['class' => 'modal-dark']
        );
    }

    public function getThemeName(): string
    {
        return 'dark';
    }
}

class LightThemeFactory implements UIAbstractFactoryInterface
{
    public function createButton(string $content): UIComponent
    {
        return new UIComponent(
            type: 'button',
            theme: 'light',
            styles: [
                'background-color' => '#ffffff',
                'color' => '#2d3748',
                'border' => '1px solid #e2e8f0',
                'padding' => '8px 16px',
                'border-radius' => '4px',
                'cursor' => 'pointer'
            ],
            content: $content,
            attributes: ['class' => 'btn-light']
        );
    }

    public function createCard(string $content): UIComponent
    {
        return new UIComponent(
            type: 'div',
            theme: 'light',
            styles: [
                'background-color' => '#ffffff',
                'color' => '#2d3748',
                'border' => '1px solid #e2e8f0',
                'padding' => '16px',
                'border-radius' => '8px',
                'box-shadow' => '0 2px 4px rgba(0, 0, 0, 0.1)'
            ],
            content: $content,
            attributes: ['class' => 'card-light']
        );
    }

    public function createModal(string $content): UIComponent
    {
        return new UIComponent(
            type: 'div',
            theme: 'light',
            styles: [
                'background-color' => '#ffffff',
                'color' => '#2d3748',
                'border' => '2px solid #e2e8f0',
                'padding' => '24px',
                'border-radius' => '12px',
                'box-shadow' => '0 10px 25px rgba(0, 0, 0, 0.15)',
                'position' => 'fixed',
                'top' => '50%',
                'left' => '50%',
                'transform' => 'translate(-50%, -50%)'
            ],
            content: $content,
            attributes: ['class' => 'modal-light']
        );
    }

    public function getThemeName(): string
    {
        return 'light';
    }
}

class ColorfulThemeFactory implements UIAbstractFactoryInterface
{
    public function createButton(string $content): UIComponent
    {
        return new UIComponent(
            type: 'button',
            theme: 'colorful',
            styles: [
                'background' => 'linear-gradient(45deg, #ff6b6b, #4ecdc4)',
                'color' => '#ffffff',
                'border' => 'none',
                'padding' => '10px 20px',
                'border-radius' => '25px',
                'cursor' => 'pointer',
                'font-weight' => 'bold'
            ],
            content: $content,
            attributes: ['class' => 'btn-colorful']
        );
    }

    public function createCard(string $content): UIComponent
    {
        return new UIComponent(
            type: 'div',
            theme: 'colorful',
            styles: [
                'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'color' => '#ffffff',
                'border' => 'none',
                'padding' => '20px',
                'border-radius' => '15px',
                'box-shadow' => '0 8px 32px rgba(102, 126, 234, 0.3)'
            ],
            content: $content,
            attributes: ['class' => 'card-colorful']
        );
    }

    public function createModal(string $content): UIComponent
    {
        return new UIComponent(
            type: 'div',
            theme: 'colorful',
            styles: [
                'background' => 'linear-gradient(45deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%)',
                'color' => '#2d3748',
                'border' => 'none',
                'padding' => '30px',
                'border-radius' => '20px',
                'box-shadow' => '0 15px 35px rgba(255, 154, 158, 0.4)',
                'position' => 'fixed',
                'top' => '50%',
                'left' => '50%',
                'transform' => 'translate(-50%, -50%)'
            ],
            content: $content,
            attributes: ['class' => 'modal-colorful']
        );
    }

    public function getThemeName(): string
    {
        return 'colorful';
    }
}

class UIAbstractFactory
{
    private static array $factories = [
        'dark' => DarkThemeFactory::class,
        'light' => LightThemeFactory::class,
        'colorful' => ColorfulThemeFactory::class,
    ];

    public static function getFactory(string $theme): UIAbstractFactoryInterface
    {
        if (!isset(self::$factories[$theme])) {
            throw new \InvalidArgumentException("Unsupported theme: {$theme}");
        }

        $factoryClass = self::$factories[$theme];
        return new $factoryClass();
    }

    public static function getSupportedThemes(): array
    {
        return array_keys(self::$factories);
    }

    public static function registerFactory(string $theme, string $factoryClass): void
    {
        if (!is_subclass_of($factoryClass, UIAbstractFactoryInterface::class)) {
            throw new \InvalidArgumentException("Factory must implement UIAbstractFactoryInterface");
        }

        self::$factories[$theme] = $factoryClass;
    }
}
