<?php

namespace Tests\\Feature;

use App\\Models\\SmtpSetting;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Http\\Client\\Request as ClientRequest;
use Illuminate\\Http\\UploadedFile;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Http;
use Tests\\TestCase;

class AdminMailSettingsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_mailbox_is_created_by_the_scoped_vps_provider_and_credentials_are_encrypted(): void
    {
        $this->withoutMiddleware();

        config([
            'mail-provider.api_url' => 'https://mail-api.example.test',
            'mail-provider.api_token' => 'project-scoped-secret',
            'mail-provider.domain' => 'farajihome.com',
            'mail-provider.smtp_host' => 'mail.farajihome.com',
            'mail-provider.imap_host' => 'mail.farajihome.com',
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 'mailbox-test-1',
                    'email' => 'info@farajihome.com',
                ],
            ], 201),
        ]);

        $this->post(route('admin.smtp-settings.mailbox.create'), [
            'local_part' => 'info',
            'quota_mb' => 256,
        ])->assertRedirect(route('admin.smtp-settings.edit'))
            ->assertSessionHas('success');

        $settings = SmtpSetting::query()->firstOrFail();

        $this->assertSame('info@farajihome.com', $settings->mailbox_address);
        $this->assertSame('info@farajihome.com', $settings->username);
        $this->assertSame('info@farajihome.com', $settings->imap_username);
        $this->assertSame($settings->password, $settings->imap_password);
        $this->assertSame('mail.farajihome.com', $settings->host);
        $this->assertSame('mail.farajihome.com', $settings->imap_host);
        $this->assertTrue($settings->enabled);
        $this->assertNotSame($settings->password, DB::table('smtp_settings')->value('password'));

        Http::assertSent(function (ClientRequest $request): bool {
            return str_ends_with($request->url(), '/api/v1/mailboxes')
                && $request->hasHeader('Authorization', 'Bearer project-scoped-secret')
                && $request['email'] === 'info@farajihome.com'
                && $request['quota_mb'] === 256
                && filled($request['password']);
        });
    }

    public function test_infor_verification_file_is_written_to_the_public_document_root(): void
    {
        $this->withoutMiddleware();

        $path = public_path('infor');
        $hadExistingFile = is_file($path);
        $existingContents = $hadExistingFile ? file_get_contents($path) : null;

        try {
            $this->post(route('admin.smtp-settings.infor.upload'), [
                'verification_file' => UploadedFile::fake()->create('infor', 1, 'text/plain'),
            ])->assertRedirect()
                ->assertSessionHas('success');

            $this->assertFileExists($path);
            $this->assertSame(url('/infor'), url('/infor'));
        } finally {
            if ($hadExistingFile) {
                file_put_contents($path, $existingContents);
            } elseif (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_infor_upload_rejects_unexpected_file_names(): void
    {
        $this->withoutMiddleware();

        $this->post(route('admin.smtp-settings.infor.upload'), [
            'verification_file' => UploadedFile::fake()->create('unexpected.txt', 1, 'text/plain'),
        ])->assertRedirect()
            ->assertSessionHasErrors('verification_file');
    }
}
