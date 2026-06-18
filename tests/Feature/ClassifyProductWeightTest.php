<?php

namespace Tests\Feature;

use App\Models\Platform;
use App\Models\Product;
use App\Models\User;
use App\Services\ImageClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the image-classifier integration in CartController::store():
 * a scraped product's category is predicted from its image, and its weight
 * is derived from that category via the services.classifier.weights map.
 *
 * Only ImageClassifier::topLabel() (which downloads the image + hits the Node
 * daemon) is faked; the real weightForLabel() runs against the config map so
 * the mapping itself is under test.
 */
class ClassifyProductWeightTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Platform $platform;

    protected function setUp(): void
    {
        parent::setUp();

        // currency/locale drive AppCustomizationMiddleware (which sets the
        // active currency); a bare factory user leaves them null in-memory.
        $this->user = User::factory()->create([
            'currency' => 'SAR',
            'locale'   => 'en',
        ]);

        $this->platform = Platform::create([
            'name'            => 'Test Platform',
            'url'             => 'http://testplatform.com',
            'logo'            => 'logo.png',
            'currency'        => 'SAR',
            'currency_symbol' => 'SAR',
            'country'         => 'SA',
            'script_file'     => 'script.js',
        ]);

        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Load the first scrape fixture from tests/selectors-test.json.
     *
     * @return array{url: string, selectors: array}
     */
    private function scrapePayload(): array
    {
        $fixture = json_decode(file_get_contents(base_path('tests/selectors-test.json')), true);

        return [
            'url'       => $fixture[0]['url'],
            'selectors' => $fixture[0]['selectors'],
        ];
    }

    public function test_scraped_product_category_and_weight_come_from_the_classifier(): void
    {
        // The fixture is a MacBook listing, so the model would predict "laptops".
        $this->partialMock(ImageClassifier::class, function ($mock) {
            $mock->shouldReceive('topLabel')->once()->andReturn('laptops');
        });

        $response = $this->postJson(
            route('carts.store', $this->platform),
            $this->scrapePayload()
        );

        $response->assertStatus(200);

        // "laptops" => 2200g in config/services.php classifier.weights.
        $this->assertDatabaseHas('products', [
            'platform_id' => $this->platform->id,
            'category'    => 'laptops',
            'weight'      => 2200,
        ]);
    }

    public function test_weight_falls_back_to_default_when_classifier_returns_nothing(): void
    {
        // Image unreachable / daemon down => topLabel() yields null.
        $this->partialMock(ImageClassifier::class, function ($mock) {
            $mock->shouldReceive('topLabel')->once()->andReturnNull();
        });

        $response = $this->postJson(
            route('carts.store', $this->platform),
            $this->scrapePayload()
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'platform_id' => $this->platform->id,
            'weight'      => Product::DEFAULT_WEIGHT_GRAMS,
        ]);
    }

    public function test_webp_images_are_transcoded_to_jpeg_before_classification(): void
    {
        // Storefronts (Amazon, …) serve WebP, which tfjs-node cannot decode.
        // Build a tiny WebP and serve it as the product image.
        $gd = imagecreatetruecolor(16, 16);
        ob_start();
        imagewebp($gd);
        $webp = ob_get_clean();
        imagedestroy($gd);

        $this->assertSame(IMAGETYPE_WEBP, getimagesizefromstring($webp)[2], 'fixture should be WebP');

        Http::fake(['*' => Http::response($webp, 200, ['Content-Type' => 'image/webp'])]);

        // Mock only the daemon call (classify); the real download + transcode
        // run, so we can inspect the format of the file actually handed over.
        $deliveredType = null;
        $classifier = $this->partialMock(ImageClassifier::class, function ($mock) use (&$deliveredType) {
            $mock->shouldReceive('classify')->once()->andReturnUsing(function (array $files) use (&$deliveredType) {
                // Inspect while the temp file still exists (classifyUrl unlinks it after).
                $deliveredType = getimagesizefromstring(file_get_contents($files[0]))[2];

                return [[
                    'file' => $files[0],
                    'predictions' => [['className' => 'laptops', 'probability' => 0.99]],
                    'error' => null,
                ]];
            });
        });

        $label = $classifier->topLabel('https://m.media-amazon.com/images/I/anything._FMwebp_.jpg');

        $this->assertSame('laptops', $label);
        $this->assertSame(IMAGETYPE_JPEG, $deliveredType, 'WebP must be transcoded to JPEG before classifying');
    }

    public function test_classifier_maps_each_known_label_to_its_configured_weight(): void
    {
        $classifier = app(ImageClassifier::class);

        foreach (config('services.classifier.weights') as $label => $grams) {
            $this->assertSame(
                (int) $grams,
                $classifier->weightForLabel($label),
                "Label [{$label}] should map to {$grams}g"
            );
        }

        // Unknown / null labels have no weight, so callers fall back.
        $this->assertNull($classifier->weightForLabel('not-a-real-label'));
        $this->assertNull($classifier->weightForLabel(null));
    }
}
