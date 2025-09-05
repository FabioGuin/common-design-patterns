<?php

namespace App\Models;

class UIComponent
{
    public string $type;
    public string $theme;
    public array $styles;
    public string $content;
    public array $attributes;

    public function __construct(
        string $type,
        string $theme,
        array $styles = [],
        string $content = '',
        array $attributes = []
    ) {
        $this->type = $type;
        $this->theme = $theme;
        $this->styles = $styles;
        $this->content = $content;
        $this->attributes = $attributes;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'theme' => $this->theme,
            'styles' => $this->styles,
            'content' => $this->content,
            'attributes' => $this->attributes,
            'created_at' => now()->toDateTimeString()
        ];
    }

    public function render(): string
    {
        $styleString = '';
        foreach ($this->styles as $property => $value) {
            $styleString .= "{$property}: {$value}; ";
        }

        $attributesString = '';
        foreach ($this->attributes as $key => $value) {
            $attributesString .= "{$key}=\"{$value}\" ";
        }

        return "<{$this->type} style=\"{$styleString}\" {$attributesString}>{$this->content}</{$this->type}>";
    }

    public function getThemeColor(): string
    {
        return $this->styles['background-color'] ?? '#ffffff';
    }

    public function isDarkTheme(): bool
    {
        return $this->theme === 'dark';
    }
}
