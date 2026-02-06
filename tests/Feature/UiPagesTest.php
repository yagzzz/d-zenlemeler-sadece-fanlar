<?php

namespace Tests\Feature;

use App\Models\CreatorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_page_renders(): void
    {
        config()->set('features.flags.ui', true);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('data-page="feed"', false);
    }

    public function test_creator_page_renders(): void
    {
        config()->set('features.flags.ui', true);

        $creator = User::factory()->creator()->create([
            'name' => 'Demo Creator',
            'username' => 'demo-creator',
        ]);

        CreatorProfile::create([
            'user_id' => $creator->id,
            'tagline' => 'Creator tagline',
            'categories' => ['art'],
            'tags' => ['tag1'],
            'social_links' => ['website' => 'https://example.com'],
        ]);

        $response = $this->get('/c/demo-creator');

        $response->assertStatus(200);
        $response->assertSee('data-page="creator"', false);
        $response->assertSee('demo-creator');
    }

    public function test_cta_label_mapping_is_available(): void
    {
        config()->set('features.flags.ui', true);

        $response = $this->get('/');
        $rendered = $response->getContent();

        preg_match('/<script id="cta-labels" type="application\/json">\s*(.*?)\s*<\/script>/s', $rendered, $matches);
        $labels = json_decode($matches[1] ?? '{}', true);

        $this->assertSame('Satın Al', $labels['ppv_required'] ?? null);
    }

    public function test_content_card_has_locked_cta_marker(): void
    {
        config()->set('features.flags.ui', true);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('cta-button');
        $response->assertSee('locked-overlay');
    }
}
