<?php

namespace Tests\Feature\Notifications;

use App\Models\NotificationTemplate;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminNotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorised_admin_can_manage_templates(): void
    {
        $this->seed(
            NotificationTemplateSeeder::class
        );

        $permission = Permission::findOrCreate(
            'notifications.templates.manage',
            'web'
        );

        $admin = User::factory()->create();

        $admin->givePermissionTo($permission);

        $template = NotificationTemplate::query()
            ->firstOrFail();

        $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.notifications.templates.index'
                )
            )
            ->assertOk();

        $this
            ->actingAs($admin)
            ->put(
                route(
                    'admin.notifications.templates.update',
                    $template
                ),
                [
                    'name' => $template->name,
                    'subject' => 'Updated subject',
                    'in_app_body' => $template->in_app_body,
                    'email_body' => $template->email_body,
                    'sms_body' => $template->sms_body,
                ]
            )
            ->assertRedirect();

        $this->assertDatabaseHas(
            'notification_templates',
            [
                'id' => $template->id,
                'subject' => 'Updated subject',
            ]
        );
    }

    public function test_unprivileged_user_cannot_manage_templates(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(
                route(
                    'admin.notifications.templates.index'
                )
            )
            ->assertForbidden();
    }
}
