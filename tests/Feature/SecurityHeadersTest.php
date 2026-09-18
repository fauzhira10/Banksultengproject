<?php

namespace Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_include_security_headers(): void
    {
        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_header_is_sent_over_https(): void
    {
        $this->get('https://localhost/admin/login')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_admin_panel_uses_strict_authorization(): void
    {
        $this->assertTrue(Filament::getPanel('admin')->isAuthorizationStrict());
    }
}
