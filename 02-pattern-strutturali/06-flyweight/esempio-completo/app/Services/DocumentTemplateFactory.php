<?php

namespace App\Services;

class DocumentTemplateFactory
{
    private array $templates = [];
    private int $createdCount = 0;
    private int $reusedCount = 0;

    /**
     * Ottiene un template, creandolo se necessario
     */
    public function getTemplate(string $name, string $layout, string $style): DocumentTemplate
    {
        $key = $this->generateKey($name, $layout, $style);

        if (!isset($this->templates[$key])) {
            $this->templates[$key] = $this->createTemplate($name, $layout, $style);
            $this->createdCount++;
            
            \Log::info('Template created', [
                'key' => $key,
                'name' => $name,
                'layout' => $layout,
                'style' => $style,
                'total_created' => $this->createdCount
            ]);
        } else {
            $this->reusedCount++;
            
            \Log::info('Template reused', [
                'key' => $key,
                'name' => $name,
                'total_reused' => $this->reusedCount
            ]);
        }

        return $this->templates[$key];
    }

    /**
     * Crea un nuovo template
     */
    private function createTemplate(string $name, string $layout, string $style): DocumentTemplate
    {
        $templateConfig = $this->getTemplateConfig($name, $layout, $style);

        return new DocumentTemplate(
            $templateConfig['name'],
            $templateConfig['layout'],
            $templateConfig['style'],
            $templateConfig['font_family'],
            $templateConfig['color_scheme'],
            $templateConfig['sections'],
            $templateConfig['footer'],
            $templateConfig['header']
        );
    }

    /**
     * Genera una chiave unica per il template
     */
    private function generateKey(string $name, string $layout, string $style): string
    {
        return md5($name . $layout . $style);
    }

    /**
     * Ottiene la configurazione del template
     */
    private function getTemplateConfig(string $name, string $layout, string $style): array
    {
        $configs = [
            'business' => [
                'single-column' => [
                    'formal' => [
                        'name' => 'Business Formal',
                        'font_family' => 'Times New Roman, serif',
                        'color_scheme' => '#000000',
                        'sections' => [
                            [
                                'title' => 'Executive Summary',
                                'class' => 'executive-summary',
                                'content' => 'This document provides a comprehensive overview of {subject}.'
                            ],
                            [
                                'title' => 'Analysis',
                                'class' => 'analysis',
                                'content' => 'Based on our analysis of {data}, we recommend {recommendation}.'
                            ],
                            [
                                'title' => 'Conclusion',
                                'class' => 'conclusion',
                                'content' => 'In conclusion, {conclusion}.'
                            ]
                        ],
                        'header' => '<h1>{company_name}</h1><p>{date}</p>',
                        'footer' => '<p>Confidential Document - {page_number}</p>'
                    ],
                    'casual' => [
                        'name' => 'Business Casual',
                        'font_family' => 'Arial, sans-serif',
                        'color_scheme' => '#333333',
                        'sections' => [
                            [
                                'title' => 'Overview',
                                'class' => 'overview',
                                'content' => 'Here\'s what we found about {subject}.'
                            ],
                            [
                                'title' => 'Key Points',
                                'class' => 'key-points',
                                'content' => 'The main points are: {key_points}.'
                            ]
                        ],
                        'header' => '<h1>{title}</h1>',
                        'footer' => '<p>Generated on {date}</p>'
                    ]
                ],
                'two-column' => [
                    'formal' => [
                        'name' => 'Business Two Column',
                        'font_family' => 'Times New Roman, serif',
                        'color_scheme' => '#000000',
                        'sections' => [
                            [
                                'title' => 'Introduction',
                                'class' => 'introduction',
                                'content' => 'This document covers {subject}.'
                            ],
                            [
                                'title' => 'Details',
                                'class' => 'details',
                                'content' => 'The details are: {details}.'
                            ],
                            [
                                'title' => 'Recommendations',
                                'class' => 'recommendations',
                                'content' => 'We recommend: {recommendations}.'
                            ],
                            [
                                'title' => 'Next Steps',
                                'class' => 'next-steps',
                                'content' => 'Next steps: {next_steps}.'
                            ]
                        ],
                        'header' => '<h1>{company_name}</h1>',
                        'footer' => '<p>Page {page_number} of {total_pages}</p>'
                    ]
                ]
            ],
            'creative' => [
                'single-column' => [
                    'modern' => [
                        'name' => 'Creative Modern',
                        'font_family' => 'Helvetica, sans-serif',
                        'color_scheme' => '#2c3e50',
                        'sections' => [
                            [
                                'title' => 'Concept',
                                'class' => 'concept',
                                'content' => 'Our concept is: {concept}.'
                            ],
                            [
                                'title' => 'Inspiration',
                                'class' => 'inspiration',
                                'content' => 'Inspired by: {inspiration}.'
                            ],
                            [
                                'title' => 'Implementation',
                                'class' => 'implementation',
                                'content' => 'We will implement: {implementation}.'
                            ]
                        ],
                        'header' => '<h1 style="color: #e74c3c;">{project_name}</h1>',
                        'footer' => '<p style="color: #7f8c8d;">Creative Project - {year}</p>'
                    ]
                ],
                'three-column' => [
                    'modern' => [
                        'name' => 'Creative Three Column',
                        'font_family' => 'Helvetica, sans-serif',
                        'color_scheme' => '#2c3e50',
                        'sections' => [
                            [
                                'title' => 'Ideas',
                                'class' => 'ideas',
                                'content' => 'Ideas: {ideas}.'
                            ],
                            [
                                'title' => 'Sketches',
                                'class' => 'sketches',
                                'content' => 'Sketches: {sketches}.'
                            ],
                            [
                                'title' => 'Prototypes',
                                'class' => 'prototypes',
                                'content' => 'Prototypes: {prototypes}.'
                            ],
                            [
                                'title' => 'Testing',
                                'class' => 'testing',
                                'content' => 'Testing: {testing}.'
                            ],
                            [
                                'title' => 'Feedback',
                                'class' => 'feedback',
                                'content' => 'Feedback: {feedback}.'
                            ],
                            [
                                'title' => 'Final',
                                'class' => 'final',
                                'content' => 'Final: {final}.'
                            ]
                        ],
                        'header' => '<h1 style="color: #e74c3c;">{project_name}</h1>',
                        'footer' => '<p style="color: #7f8c8d;">Creative Process - {date}</p>'
                    ]
                ]
            ],
            'technical' => [
                'single-column' => [
                    'monospace' => [
                        'name' => 'Technical Monospace',
                        'font_family' => 'Courier New, monospace',
                        'color_scheme' => '#2c3e50',
                        'sections' => [
                            [
                                'title' => 'Specifications',
                                'class' => 'specifications',
                                'content' => 'Specifications: {specifications}.'
                            ],
                            [
                                'title' => 'Implementation',
                                'class' => 'implementation',
                                'content' => 'Implementation: {implementation}.'
                            ],
                            [
                                'title' => 'Testing',
                                'class' => 'testing',
                                'content' => 'Testing: {testing}.'
                            ]
                        ],
                        'header' => '<h1>{project_name}</h1><p>Technical Documentation</p>',
                        'footer' => '<p>Version {version} - {date}</p>'
                    ]
                ]
            ]
        ];

        return $configs[$name][$layout][$style] ?? $this->getDefaultConfig();
    }

    /**
     * Ottiene la configurazione di default
     */
    private function getDefaultConfig(): array
    {
        return [
            'name' => 'Default Template',
            'font_family' => 'Arial, sans-serif',
            'color_scheme' => '#000000',
            'sections' => [
                [
                    'title' => 'Content',
                    'class' => 'content',
                    'content' => 'Default content: {content}.'
                ]
            ],
            'header' => '<h1>{title}</h1>',
            'footer' => '<p>{date}</p>'
        ];
    }

    /**
     * Ottiene le statistiche della factory
     */
    public function getStats(): array
    {
        return [
            'total_templates' => count($this->templates),
            'created_count' => $this->createdCount,
            'reused_count' => $this->reusedCount,
            'reuse_ratio' => $this->createdCount > 0 ? $this->reusedCount / $this->createdCount : 0,
        ];
    }

    /**
     * Ottiene tutti i template creati
     */
    public function getAllTemplates(): array
    {
        return $this->templates;
    }

    /**
     * Pulisce la cache dei template
     */
    public function clearCache(): void
    {
        $this->templates = [];
        $this->createdCount = 0;
        $this->reusedCount = 0;
        
        \Log::info('Template cache cleared');
    }
}
