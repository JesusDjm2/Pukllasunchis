<?php

namespace Tests\Feature;

use Tests\TestCase;

class PukllaBotTest extends TestCase
{
    public function test_pukllabot_chat_route_exists(): void
    {
        $this->assertStringContainsString('pukllabot/chat', route('pukllabot.chat', [], false));
    }

    public function test_chat_validates_message_required(): void
    {
        $response = $this->postJson(route('pukllabot.chat'), []);

        $response->assertStatus(422);
    }

    public function test_chat_returns_503_when_openrouter_key_empty(): void
    {
        config(['pukllabot.openrouter.api_key' => '']);

        $response = $this->postJson(route('pukllabot.chat'), [
            'message' => 'Hola de prueba',
        ]);

        $response->assertStatus(503);
        $response->assertJsonStructure(['error']);
    }

    public function test_pukllabot_partial_renders(): void
    {
        $html = view('partials.pukllabot-widget')->render();

        $this->assertStringContainsString('pukllabot-root', $html);
        $this->assertStringContainsString('pukllabot/chat', $html);
        $this->assertStringContainsString('data-csrf', $html);
        $this->assertStringContainsString('pukllabot-scope', $html);
        $this->assertStringContainsString('pukllabot-faq-wrap', $html);
    }

    public function test_chat_rejects_invalid_scope(): void
    {
        $response = $this->postJson(route('pukllabot.chat'), [
            'message' => 'Hola',
            'scope' => 'tema-inexistente',
        ]);

        $response->assertStatus(422);
    }
}
