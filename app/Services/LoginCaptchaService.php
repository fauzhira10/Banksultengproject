<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Str;

/**
 * Captcha gambar untuk halaman login, dibuat sendiri dengan GD tanpa layanan pihak ketiga
 * sehingga tetap berfungsi di jaringan intranet.
 *
 * Kode hanya disimpan di session dalam bentuk HMAC, berlaku singkat, dan hangus setelah
 * satu kali verifikasi (benar maupun salah).
 */
class LoginCaptchaService
{
    public const SESSION_KEY = 'login_captcha';

    public const CODE_LENGTH = 5;

    public const TTL_SECONDS = 300;

    /**
     * Karakter yang mudah dibedakan secara visual (tanpa 0/O dan 1/I/L).
     */
    protected const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected const WIDTH = 180;

    protected const HEIGHT = 56;

    /**
     * Membuat kode baru, menyimpannya di session, dan mengembalikan gambarnya sebagai data URI PNG.
     */
    public function generate(): string
    {
        $code = $this->generateCode();

        session()->put(self::SESSION_KEY, [
            'hash' => $this->hashCode($code),
            'expires_at' => now()->addSeconds(self::TTL_SECONDS)->getTimestamp(),
        ]);

        return 'data:image/png;base64,'.base64_encode($this->render($code));
    }

    /**
     * Memverifikasi input pengguna (tidak peka huruf besar/kecil). Kode langsung hangus setelah dicek.
     */
    public function verify(?string $input): bool
    {
        $captcha = session()->pull(self::SESSION_KEY);

        if (! is_array($captcha) || blank($input) || now()->getTimestamp() > ($captcha['expires_at'] ?? 0)) {
            return false;
        }

        return hash_equals((string) ($captcha['hash'] ?? ''), $this->hashCode($input));
    }

    protected function generateCode(): string
    {
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $code;
    }

    protected function hashCode(string $code): string
    {
        return hash_hmac('sha256', Str::upper(trim($code)), (string) config('app.key'));
    }

    protected function render(string $code): string
    {
        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagefill($image, 0, 0, imagecolorallocate($image, 241, 245, 249));

        $this->drawNoiseLines($image, 3);

        $slotWidth = intdiv(self::WIDTH - 20, strlen($code));

        foreach (str_split($code) as $index => $character) {
            $glyph = $this->renderGlyph($character);
            $targetWidth = random_int(28, 32);
            $targetHeight = random_int(40, 46);

            imagecopyresampled(
                $image,
                $glyph,
                10 + ($index * $slotWidth) + random_int(0, 4),
                random_int(4, self::HEIGHT - $targetHeight - 4),
                0,
                0,
                $targetWidth,
                $targetHeight,
                imagesx($glyph),
                imagesy($glyph),
            );
        }

        $this->drawNoiseLines($image, 2);
        $this->drawNoiseDots($image, 300);

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    /**
     * Satu karakter dengan font bawaan GD, diputar acak di atas latar transparan.
     */
    protected function renderGlyph(string $character): GdImage
    {
        $glyph = imagecreatetruecolor(15, 20);
        imagealphablending($glyph, false);
        imagesavealpha($glyph, true);

        $transparent = imagecolorallocatealpha($glyph, 0, 0, 0, 127);
        imagefill($glyph, 0, 0, $transparent);

        $color = imagecolorallocate($glyph, random_int(10, 40), random_int(20, 50), random_int(60, 110));
        imagestring($glyph, 5, 2, 2, $character, $color);
        imagestring($glyph, 5, 3, 2, $character, $color);

        $rotated = imagerotate($glyph, random_int(-20, 20), $transparent);
        imagesavealpha($rotated, true);

        return $rotated;
    }

    protected function drawNoiseLines(GdImage $image, int $count): void
    {
        imagesetthickness($image, 2);

        for ($i = 0; $i < $count; $i++) {
            $color = imagecolorallocate($image, random_int(100, 180), random_int(110, 190), random_int(130, 210));

            imageline(
                $image,
                0,
                random_int(0, self::HEIGHT),
                self::WIDTH,
                random_int(0, self::HEIGHT),
                $color,
            );
        }
    }

    protected function drawNoiseDots(GdImage $image, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $color = imagecolorallocate($image, random_int(80, 200), random_int(80, 200), random_int(80, 200));

            imagesetpixel($image, random_int(0, self::WIDTH - 1), random_int(0, self::HEIGHT - 1), $color);
        }
    }
}
