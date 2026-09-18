<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use App\Services\LoginCaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginCaptchaTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_captcha_image(): void
    {
        $this->get('/admin/login')
            ->assertSuccessful()
            ->assertSee('data:image/png;base64,', escape: false)
            ->assertSee('Kode Captcha');
    }

    public function test_correct_password_with_wrong_captcha_is_rejected(): void
    {
        User::factory()->create([
            'username' => 'petugas',
            'password' => bcrypt('password123'),
        ]);
        $this->fakeLoginCaptcha('ABCDE');

        Livewire::test(Login::class)
            ->fillForm([
                'username' => 'petugas',
                'password' => 'password123',
                'captcha' => 'ZZZZZ',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['captcha']);

        $this->assertGuest();
    }

    public function test_captcha_is_case_insensitive(): void
    {
        $user = User::factory()->create([
            'username' => 'petugas',
            'password' => bcrypt('password123'),
        ]);
        $this->fakeLoginCaptcha('ABCDE');

        Livewire::test(Login::class)
            ->fillForm([
                'username' => 'petugas',
                'password' => 'password123',
                'captcha' => 'abcde',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_captcha_is_replaced_after_every_login_attempt(): void
    {
        $component = Livewire::test(Login::class);
        $firstImage = $component->get('captchaImage');

        $component
            ->fillForm([
                'username' => 'tidak-ada',
                'password' => 'salah',
                'captcha' => 'SALAH',
            ])
            ->call('authenticate')
            ->assertSet('data.captcha', null);

        $this->assertNotSame($firstImage, $component->get('captchaImage'));
    }

    public function test_captcha_code_can_only_be_verified_once(): void
    {
        $code = $this->fakeLoginCaptcha('ABCDE');
        $service = app(LoginCaptchaService::class);

        $service->generate();

        $this->assertTrue($service->verify($code));
        $this->assertFalse($service->verify($code));
    }

    public function test_expired_captcha_is_rejected(): void
    {
        $code = $this->fakeLoginCaptcha('ABCDE');
        $service = app(LoginCaptchaService::class);

        $service->generate();
        $this->travel(LoginCaptchaService::TTL_SECONDS + 1)->seconds();

        $this->assertFalse($service->verify($code));
    }

    public function test_plain_captcha_code_is_never_stored_in_session(): void
    {
        $code = $this->fakeLoginCaptcha('ABCDE');

        app(LoginCaptchaService::class)->generate();

        $this->assertStringNotContainsString($code, (string) json_encode(session()->all()));
    }
}
