<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableScrollToEndButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_scroll_to_end_button_is_rendered_on_tiket_list_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/tikets');

        $response->assertSuccessful();
        $response->assertSee('data-bs-scroll-end', false);
    }

    public function test_scroll_to_end_button_is_rendered_on_terminal_list_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/terminals');

        $response->assertSuccessful();
        $response->assertSee('data-bs-scroll-end', false);
    }

    public function test_scroll_to_end_button_is_not_rendered_outside_tiket_list_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/tikets/create');

        $response->assertSuccessful();
        $response->assertDontSee('data-bs-scroll-end', false);
    }
}
