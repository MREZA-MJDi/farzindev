<?php

namespace App\Console\Commands;

use App\Services\Media\ResponsiveImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OptimizeResponsiveImages extends Command
{
    protected $signature = 'farzin:images-optimize
        {--disk=public : Storage disk containing original uploads}
        {--dry-run : List eligible images without creating variants}
        {--limit=0 : Maximum number of source images to process; 0 means all}';

    protected $description = 'Create responsive WebP variants for existing uploads without changing or deleting originals.';

    public function handle(ResponsiveImageService $images): int
    {
        $diskName = (string) $this->option('disk');
        $disk = Storage::disk($diskName);

        if (! method_exists($disk, 'allFiles') || ! method_exists($disk, 'path')) {
            $this->error('The selected storage disk must support recursive listing and local file paths.');

            return self::FAILURE;
        }

        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            $this->error('PHP GD with WebP support is required. Originals have not been modified.');

            return self::FAILURE;
        }

        $limit = max(0, (int) $this->option('limit'));
        $eligible = [];

        foreach ($disk->allFiles() as $path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (
                ! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
                || preg_match('/\.w(?:480|800|1200|1600)\.webp$/i', $path)
            ) {
                continue;
            }

            $eligible[] = $path;

            if ($limit > 0 && count($eligible) >= $limit) {
                break;
            }
        }

        if ($eligible === []) {
            $this->info('No eligible original images were found.');

            return self::SUCCESS;
        }

        $this->info(count($eligible) . ' original image(s) found. Originals will be preserved.');

        if ($this->option('dry-run')) {
            $this->table(
                ['Path', 'Original size'],
                array_map(fn (string $path): array => [
                    $path,
                    $this->formatBytes((int) ($disk->size($path) ?: 0)),
                ], $eligible)
            );

            $this->comment('Dry run only. No files were written.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar(count($eligible));
        $bar->start();

        foreach ($eligible as $path) {
            try {
                $variants = $images->generateForPath($path, $diskName);
                $created += count($variants);
                $skipped += $variants === [] ? 1 : 0;
            } catch (Throwable $exception) {
                $failed++;
                report($exception);
                $this->newLine(2);
                $this->warn('Skipped ' . $path . ' because conversion failed.');
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(
            ['Result', 'Count'],
            [
                ['Original images scanned', count($eligible)],
                ['WebP variants created', $created],
                ['Images skipped (already optimized or unsupported)', $skipped],
                ['Conversion errors', $failed],
            ]
        );

        $this->comment('All original files were kept. Re-running the command is safe.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 2) . ' MB';
        }

        return number_format($bytes / 1024, 1) . ' KB';
    }
}
