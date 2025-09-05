<?php

namespace App\Services;

class DocumentTemplate
{
    private string $name;
    private string $layout;
    private string $style;
    private string $fontFamily;
    private string $colorScheme;
    private array $sections;
    private string $footer;
    private string $header;

    public function __construct(
        string $name,
        string $layout,
        string $style,
        string $fontFamily,
        string $colorScheme,
        array $sections,
        string $footer,
        string $header
    ) {
        $this->name = $name;
        $this->layout = $layout;
        $this->style = $style;
        $this->fontFamily = $fontFamily;
        $this->colorScheme = $colorScheme;
        $this->sections = $sections;
        $this->footer = $footer;
        $this->header = $header;
    }

    /**
     * Ottiene il nome del template
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Ottiene il layout del template
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Ottiene lo stile del template
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * Ottiene la famiglia di font
     */
    public function getFontFamily(): string
    {
        return $this->fontFamily;
    }

    /**
     * Ottiene lo schema colori
     */
    public function getColorScheme(): string
    {
        return $this->colorScheme;
    }

    /**
     * Ottiene le sezioni del template
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * Ottiene il footer del template
     */
    public function getFooter(): string
    {
        return $this->footer;
    }

    /**
     * Ottiene l'header del template
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * Renderizza il template con le proprietà estrinseche
     */
    public function render(array $extrinsicData): string
    {
        \Log::info('Rendering template', [
            'template' => $this->name,
            'data_keys' => array_keys($extrinsicData)
        ]);

        $html = $this->buildHtml($extrinsicData);
        
        return $html;
    }

    /**
     * Costruisce l'HTML del template
     */
    private function buildHtml(array $data): string
    {
        $html = "<div class='document {$this->style}' style='font-family: {$this->fontFamily}; color: {$this->colorScheme};'>";
        
        // Header
        if (!empty($this->header)) {
            $html .= "<header class='document-header'>";
            $html .= $this->replacePlaceholders($this->header, $data);
            $html .= "</header>";
        }

        // Layout specifico
        switch ($this->layout) {
            case 'single-column':
                $html .= $this->buildSingleColumnLayout($data);
                break;
            case 'two-column':
                $html .= $this->buildTwoColumnLayout($data);
                break;
            case 'three-column':
                $html .= $this->buildThreeColumnLayout($data);
                break;
            default:
                $html .= $this->buildDefaultLayout($data);
        }

        // Footer
        if (!empty($this->footer)) {
            $html .= "<footer class='document-footer'>";
            $html .= $this->replacePlaceholders($this->footer, $data);
            $html .= "</footer>";
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Costruisce un layout a colonna singola
     */
    private function buildSingleColumnLayout(array $data): string
    {
        $html = "<div class='single-column'>";
        
        foreach ($this->sections as $section) {
            $html .= "<section class='{$section['class']}'>";
            $html .= "<h2>{$section['title']}</h2>";
            $html .= $this->replacePlaceholders($section['content'], $data);
            $html .= "</section>";
        }
        
        $html .= "</div>";
        return $html;
    }

    /**
     * Costruisce un layout a due colonne
     */
    private function buildTwoColumnLayout(array $data): string
    {
        $html = "<div class='two-column'>";
        
        $leftSections = array_slice($this->sections, 0, ceil(count($this->sections) / 2));
        $rightSections = array_slice($this->sections, ceil(count($this->sections) / 2));
        
        $html .= "<div class='column-left'>";
        foreach ($leftSections as $section) {
            $html .= "<section class='{$section['class']}'>";
            $html .= "<h3>{$section['title']}</h3>";
            $html .= $this->replacePlaceholders($section['content'], $data);
            $html .= "</section>";
        }
        $html .= "</div>";
        
        $html .= "<div class='column-right'>";
        foreach ($rightSections as $section) {
            $html .= "<section class='{$section['class']}'>";
            $html .= "<h3>{$section['title']}</h3>";
            $html .= $this->replacePlaceholders($section['content'], $data);
            $html .= "</section>";
        }
        $html .= "</div>";
        
        $html .= "</div>";
        return $html;
    }

    /**
     * Costruisce un layout a tre colonne
     */
    private function buildThreeColumnLayout(array $data): string
    {
        $html = "<div class='three-column'>";
        
        $sectionsPerColumn = ceil(count($this->sections) / 3);
        
        for ($i = 0; $i < 3; $i++) {
            $columnSections = array_slice($this->sections, $i * $sectionsPerColumn, $sectionsPerColumn);
            $html .= "<div class='column-" . ($i + 1) . "'>";
            
            foreach ($columnSections as $section) {
                $html .= "<section class='{$section['class']}'>";
                $html .= "<h4>{$section['title']}</h4>";
                $html .= $this->replacePlaceholders($section['content'], $data);
                $html .= "</section>";
            }
            
            $html .= "</div>";
        }
        
        $html .= "</div>";
        return $html;
    }

    /**
     * Costruisce il layout di default
     */
    private function buildDefaultLayout(array $data): string
    {
        $html = "<div class='default-layout'>";
        
        foreach ($this->sections as $section) {
            $html .= "<section class='{$section['class']}'>";
            $html .= "<h2>{$section['title']}</h2>";
            $html .= $this->replacePlaceholders($section['content'], $data);
            $html .= "</section>";
        }
        
        $html .= "</div>";
        return $html;
    }

    /**
     * Sostituisce i placeholder nel contenuto
     */
    private function replacePlaceholders(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }

    /**
     * Ottiene le informazioni del template
     */
    public function getInfo(): array
    {
        return [
            'name' => $this->name,
            'layout' => $this->layout,
            'style' => $this->style,
            'font_family' => $this->fontFamily,
            'color_scheme' => $this->colorScheme,
            'sections_count' => count($this->sections),
            'has_header' => !empty($this->header),
            'has_footer' => !empty($this->footer),
        ];
    }
}
