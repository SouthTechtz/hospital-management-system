<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Functional-only QR data URIs (wristbands, MFA enrolment). Rendering the
 * TOTP provisioning URI lets users enrol even without typing the secret.
 */
class QrCodeService
{
    public function dataUri(string $content, int $size = 240): ?string
    {
        try {
            $writer = new Writer(
                new ImageRenderer(
                    new RendererStyle($size, 4),
                    new ImagickImageBackEnd()
                )
            );

            $png = $writer->writeString($content);

            return 'data:image/png;base64,'.base64_encode($png);
        } catch (Throwable) {
            try {
                $writer = new Writer(
                    new ImageRenderer(
                        new RendererStyle($size, 4),
                        new SvgImageBackEnd()
                    )
                );

                $svg = $writer->writeString($content);

                return 'data:image/svg+xml;base64,'.base64_encode($svg);
            } catch (Throwable $e) {
                Log::warning('QR generation failed', ['error' => $e->getMessage()]);

                return null;
            }
        }
    }
}