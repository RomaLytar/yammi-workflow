<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Filament;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Facade\Approval;
use Yammi\Workflow\Filament\Pages\WorkflowDesigner;
use Yammi\Workflow\Filament\Resources\WorkflowApprovalResource\Pages\ListWorkflowApprovals;
use Yammi\Workflow\Filament\Resources\WorkflowInstanceResource\Pages\ListWorkflowInstances;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Filament\TestPanelProvider;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\Support\User;
use Yammi\Workflow\Tests\TestCase;

final class FilamentRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            LivewireServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('workflow.subjects', [Invoice::class => 'invoice']);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('workflow_state')->nullable();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('testing'));

        $this->actingAs(User::create(['name' => 'Admin']));

        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'states' => ['draft', 'pending', 'approved'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending'], 'pending' => ['approved']],
        ]));
    }

    public function test_the_instances_page_renders(): void
    {
        Invoice::create()->transitionTo('pending');

        Livewire::test(ListWorkflowInstances::class)->assertOk();
    }

    public function test_the_approvals_page_renders(): void
    {
        $invoice = Invoice::create();
        Approval::request($invoice, ['manager', 'finance']);

        Livewire::test(ListWorkflowApprovals::class)->assertOk();
    }

    public function test_the_designer_page_renders(): void
    {
        Livewire::test(WorkflowDesigner::class)->assertOk();
    }

    public function test_the_designer_saves_a_new_version(): void
    {
        Livewire::test(WorkflowDesigner::class)->call('save');

        $this->assertDatabaseHas('workflows', ['key' => 'invoice', 'version' => 2, 'is_current' => true]);
    }
}
