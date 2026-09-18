<?php

namespace Tests;

use App\Services\LoginCaptchaService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    /**
     * Membuat captcha login selalu berisi kode yang diketahui. Hanya pembuatan kode acak
     * yang dipalsukan; penyimpanan dan verifikasi tetap memakai implementasi asli.
     */
    protected function fakeLoginCaptcha(string $code = 'ABCDE'): string
    {
        $this->partialMock(LoginCaptchaService::class, function (MockInterface $mock) use ($code): void {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('generateCode')
                ->andReturn($code);
        });

        return $code;
    }
}
