<?php

namespace App\Filament\App\Pages;

use App\Models\TeamIntegration;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Liberu\Foundation\Organizations\Models\Team;

/**
 * @property-read Schema $form
 */
final class WorkspaceSetup extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Workspace setup';

    protected static ?int $navigationSort = -10;

    protected static ?string $title = 'Set up your workspace';

    protected string $view = 'filament-panels::pages.page';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $team = Team::query()->find($user->current_team_id);

        if (! $team instanceof Team) {
            $team = Team::query()->where('user_id', $user->getKey())->first();
            if (! $team instanceof Team) {
                $team = new Team([
                    'name' => $user->getAttribute('name').'\'s workspace',
                    'personal_team' => false,
                ]);
                $team->forceFill(['user_id' => $user->getKey()])->save();
            }
            $user->switchTeam($team);
        }

        $integration = TeamIntegration::query()
            ->where('team_id', $team->getKey())
            ->where('provider', 'genealogy-api')
            ->first();

        $this->form->fill([
            'name' => $user->getAttribute('name'),
            'email' => $user->getAttribute('email'),
            'team_name' => $team->getAttribute('name'),
            'locale' => $user->locale ?? 'en',
            'timezone' => $user->timezone ?? config('app.timezone'),
            'provider' => $integration?->label,
            'api_key_enabled' => $integration ? $integration->enabled : false,
            'api_key' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Wizard::make([
                    Step::make('Workspace')
                        ->icon('heroicon-o-home-modern')
                        ->description('Give your family history workspace a clear identity.')
                        ->schema([
                            Section::make()
                                ->description('These preferences apply to your current workspace and can be changed later.')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Your name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->label('Email address')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->helperText('Your sign-in email can be changed from your profile.'),
                                    TextInput::make('team_name')
                                        ->label('Family workspace name')
                                        ->required()
                                        ->maxLength(255),
                                    Select::make('locale')
                                        ->label('Language')
                                        ->options(['en' => 'English'])
                                        ->required()
                                        ->native(false),
                                    Select::make('timezone')
                                        ->label('Timezone')
                                        ->options(collect(\DateTimeZone::listIdentifiers())->mapWithKeys(fn (string $timezone): array => [$timezone => $timezone])->all())
                                        ->searchable()
                                        ->required()
                                        ->native(false),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ]),
                    Step::make('Sign-in & security')
                        ->icon('heroicon-o-shield-check')
                        ->description('Keep access convenient and protected.')
                        ->schema([
                            Section::make('Connect a sign-in provider')
                                ->description('Use a provider you already trust for faster sign-in. OAuth credentials are configured by the site administrator; this page only links your account.')
                                ->schema([
                                    ViewField::make('oauth_links')
                                        ->view('filament.app.pages.workspace-setup.oauth-links'),
                                ]),
                        ]),
                    Step::make('Integrations')
                        ->icon('heroicon-o-key')
                        ->description('Add an optional project API key when your workflow needs one.')
                        ->schema([
                            Section::make('Project API access')
                                ->description('Keys are encrypted before they are stored and are never shown in full after saving. This integration is optional and can be enabled later from Workspace setup.')
                                ->schema([
                                    Toggle::make('api_key_enabled')
                                        ->label('Enable project API integration')
                                        ->helperText('Turn this on only when your project workflow needs the external API.'),
                                    TextInput::make('provider')
                                        ->label('Integration label')
                                        ->placeholder('e.g. FamilySearch')
                                        ->maxLength(255),
                                    TextInput::make('api_key')
                                        ->label('API key')
                                        ->password()
                                        ->revealable()
                                        ->required(fn (Get $get): bool => $get('api_key_enabled') && ! TeamIntegration::query()
                                            ->where('team_id', auth()->user()->current_team_id)
                                            ->where('provider', 'genealogy-api')
                                            ->exists())
                                        ->maxLength(1000)
                                        ->helperText('Only enter a key intended for this workspace. Leave blank to keep the saved key.'),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ]),
                ])
                    ->submitAction('Finish setup')
                    ->contained(false),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();
        $team = Team::query()->find($user->current_team_id);
        $existingIntegration = TeamIntegration::query()
            ->where('team_id', $team?->getKey())
            ->where('provider', 'genealogy-api')
            ->first();

        abort_unless($team instanceof Team && $user->belongsToTeam($team), 403);

        DB::transaction(function () use ($data, $user, $team, $existingIntegration): void {
            $team->update(['name' => $data['team_name']]);
            $user->update([
                'name' => $data['name'],
                'locale' => $data['locale'],
                'timezone' => $data['timezone'],
                'onboarding_completed_at' => now(),
            ]);

            if ($data['api_key_enabled'] ?? false) {
                TeamIntegration::query()->updateOrCreate(
                    ['team_id' => $team->getKey(), 'provider' => 'genealogy-api'],
                    [
                        'label' => filled($data['provider']) ? $data['provider'] : 'Project API',
                        'credentials' => filled($data['api_key'])
                            ? ['api_key' => $data['api_key']]
                            : $existingIntegration?->credentials,
                        'enabled' => true,
                    ],
                );
            } else {
                TeamIntegration::query()
                    ->where('team_id', $team->getKey())
                    ->where('provider', 'genealogy-api')
                    ->update(['enabled' => false]);
            }
        });

        Notification::make()
            ->success()
            ->title('Workspace ready')
            ->body('Your workspace preferences and integration settings were saved securely.')
            ->send();

        $this->redirect(route('filament.app.pages.dashboard'));
    }
}
