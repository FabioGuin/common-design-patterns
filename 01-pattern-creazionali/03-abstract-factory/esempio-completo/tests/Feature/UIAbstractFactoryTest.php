<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\UIAbstractFactory;
use App\Models\UIComponent;

class UIAbstractFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_dark_theme_components()
    {
        $factory = UIAbstractFactory::getFactory('dark');
        
        $button = $factory->createButton('Dark Button');
        $card = $factory->createCard('Dark Card');
        $modal = $factory->createModal('Dark Modal');
        
        $this->assertInstanceOf(UIComponent::class, $button);
        $this->assertEquals('dark', $button->theme);
        $this->assertTrue($button->isDarkTheme());
        $this->assertStringContains('background-color: #2d3748', $button->render());
        
        $this->assertEquals('dark', $card->theme);
        $this->assertStringContains('background-color: #1a202c', $card->render());
        
        $this->assertEquals('dark', $modal->theme);
        $this->assertStringContains('background-color: #2d3748', $modal->render());
    }

    /** @test */
    public function it_creates_light_theme_components()
    {
        $factory = UIAbstractFactory::getFactory('light');
        
        $button = $factory->createButton('Light Button');
        $card = $factory->createCard('Light Card');
        $modal = $factory->createModal('Light Modal');
        
        $this->assertInstanceOf(UIComponent::class, $button);
        $this->assertEquals('light', $button->theme);
        $this->assertFalse($button->isDarkTheme());
        $this->assertStringContains('background-color: #ffffff', $button->render());
        
        $this->assertEquals('light', $card->theme);
        $this->assertStringContains('background-color: #ffffff', $card->render());
        
        $this->assertEquals('light', $modal->theme);
        $this->assertStringContains('background-color: #ffffff', $modal->render());
    }

    /** @test */
    public function it_creates_colorful_theme_components()
    {
        $factory = UIAbstractFactory::getFactory('colorful');
        
        $button = $factory->createButton('Colorful Button');
        $card = $factory->createCard('Colorful Card');
        $modal = $factory->createModal('Colorful Modal');
        
        $this->assertInstanceOf(UIComponent::class, $button);
        $this->assertEquals('colorful', $button->theme);
        $this->assertStringContains('linear-gradient', $button->render());
        
        $this->assertEquals('colorful', $card->theme);
        $this->assertStringContains('linear-gradient', $card->render());
        
        $this->assertEquals('colorful', $modal->theme);
        $this->assertStringContains('linear-gradient', $modal->render());
    }

    /** @test */
    public function it_throws_exception_for_unsupported_theme()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported theme: invalid');
        
        UIAbstractFactory::getFactory('invalid');
    }

    /** @test */
    public function it_returns_supported_themes()
    {
        $themes = UIAbstractFactory::getSupportedThemes();
        
        $this->assertIsArray($themes);
        $this->assertContains('dark', $themes);
        $this->assertContains('light', $themes);
        $this->assertContains('colorful', $themes);
    }

    /** @test */
    public function it_allows_registering_new_factory()
    {
        // Crea una factory personalizzata
        $customFactory = new class implements \App\Services\UIAbstractFactoryInterface {
            public function createButton(string $content): UIComponent
            {
                return new UIComponent('button', 'custom', ['background-color' => '#ff0000'], $content);
            }
            public function createCard(string $content): UIComponent
            {
                return new UIComponent('div', 'custom', ['background-color' => '#00ff00'], $content);
            }
            public function createModal(string $content): UIComponent
            {
                return new UIComponent('div', 'custom', ['background-color' => '#0000ff'], $content);
            }
            public function getThemeName(): string
            {
                return 'custom';
            }
        };
        
        // Registra la factory
        UIAbstractFactory::registerFactory('custom', get_class($customFactory));
        
        // Verifica che sia stata registrata
        $themes = UIAbstractFactory::getSupportedThemes();
        $this->assertContains('custom', $themes);
        
        // Testa la creazione
        $factory = UIAbstractFactory::getFactory('custom');
        $button = $factory->createButton('Custom Button');
        $this->assertEquals('custom', $button->theme);
        $this->assertStringContains('background-color: #ff0000', $button->render());
    }

    /** @test */
    public function it_throws_exception_for_invalid_factory_class()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Factory must implement UIAbstractFactoryInterface');
        
        UIAbstractFactory::registerFactory('invalid', \stdClass::class);
    }

    /** @test */
    public function it_creates_components_with_different_styles_per_theme()
    {
        $darkFactory = UIAbstractFactory::getFactory('dark');
        $lightFactory = UIAbstractFactory::getFactory('light');
        $colorfulFactory = UIAbstractFactory::getFactory('colorful');
        
        $darkButton = $darkFactory->createButton('Test');
        $lightButton = $lightFactory->createButton('Test');
        $colorfulButton = $colorfulFactory->createButton('Test');
        
        // Verifica che ogni tema abbia stili diversi
        $this->assertNotEquals($darkButton->getThemeColor(), $lightButton->getThemeColor());
        $this->assertNotEquals($lightButton->getThemeColor(), $colorfulButton->getThemeColor());
        $this->assertNotEquals($darkButton->styles, $lightButton->styles);
        $this->assertNotEquals($lightButton->styles, $colorfulButton->styles);
    }

    /** @test */
    public function it_creates_consistent_components_within_same_theme()
    {
        $factory = UIAbstractFactory::getFactory('dark');
        
        $button1 = $factory->createButton('Button 1');
        $button2 = $factory->createButton('Button 2');
        
        // Verifica che i componenti dello stesso tema abbiano stili consistenti
        $this->assertEquals($button1->styles, $button2->styles);
        $this->assertEquals($button1->theme, $button2->theme);
        $this->assertEquals($button1->attributes, $button2->attributes);
    }
}
