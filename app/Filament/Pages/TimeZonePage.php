<?php

namespace App\Filament\Pages;

use App\Models\Restaurant;
use App\Traits\FilamentCustomPageAuthorization;
use DateTime;
use DateTimeZone;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class TimeZonePage extends Page
{
    use FilamentCustomPageAuthorization;
    protected static ?string $model = Restaurant::class;
    protected static string $view = 'filament.pages.time-zone-page';
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Technical Settings';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'timezone';
    public ?Restaurant $record;
    public ?array $data = [];

    public function getTitle(): string | Htmlable
    {
        return __("Timezone (" . date_default_timezone_get() . ")");
    }

    public static function getNavigationLabel(): string
    {
        return __("Timezone (" . date_default_timezone_get() . ")");
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Restaurant::whereDomain(config('app.restaurant_url'))?->exists();
    }

    public function getSubheading(): string
    {
        return __('Manage and configure the application\'s timezone settings to ensure consistent time-based functionality across the system.');
    }

    public function mount()
    {
        if (Restaurant::whereDomain(config('app.restaurant_url'))?->exists()) {

            $this->record = Restaurant::first();
            $this->data = $this->record?->toArray();

            // Set default value for timezone
            if (!isset($this->data['timezone'])) {
                $this->data['timezone'] = config('app.timezone');
            }

            $this->form->fill($this->data);
        } else {
            Notification::make()
                ->title('Restaurant Details Missing')
                ->body('Please configure and save your restaurant details before setting the timezone for the application.')
                ->warning()
                ->send();
            return redirect()->to(RestaurantDetails::getUrl());
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getFormActions()['store'],
        ];

    }

    protected function getFormActions(): array
    {
        return [
            'store' => Actions\Action::make('saveRecord')
                ->label('Save Changes')
                ->authorize(empty($this->record) ? static::canCreate(): static::canEdit($this->record))
                ->formId('timezone')
                ->extraAttributes(['type' => 'submit'])
                ->action('save'),
        ];
    }

    public function save()
    {
        $existingData = Restaurant::first();
        $data = $this->form->getState();

        if ($existingData) {
            $data['updated_by_user_id'] = auth()->id();

            $existingData->update($data);

            // Fetch the updated instance
            $this->record = $existingData->fresh();
        } else {
            $data['updated_by_user_id'] = auth()->id();
            $data['created_by_user_id'] = auth()->id();
            $this->record = Restaurant::create($data);
        }

        Notification::make('save_record')
            ->title('Success!')
            ->body('Timezone Saved Successfully!')
            ->success()
            ->send();

        // Check if app timezone changes then update in .env file
        if (!empty($data['timezone']) && $data['timezone'] != config('app.timezone')) {
            update_env_value('APP_TIMEZONE', $this->record->timezone);
            return redirect()->to(URL::previous());
        }

    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->model(static::getModel())
            ->schema([
                Forms\Components\Section::make('Current DateTime')
                    ->description(fn() => Carbon::now()->format('M d, Y h:i A T (l)'))
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-blue-950/20 ring-0 dark:ring-0'])
                    ->columnSpan(['lg' => 12])
                    ->schema([
                        Forms\Components\Select::make('timezone')
                            ->label('Timezone')
                            ->prefixIcon('heroicon-o-clock')
                            ->options(collect(DateTimeZone::listIdentifiers())->mapWithKeys(function ($timezone) {
                                $dateTimeZone = new DateTimeZone($timezone);
                                $dateTime = new DateTime('now', $dateTimeZone);
                                $offset = $dateTimeZone->getOffset($dateTime);
                                $formattedOffset = sprintf('%+03d:%02d', $offset / 3600, abs($offset % 3600) / 60);

                                return [$timezone => "(UTC $formattedOffset) $timezone"];
                            }))
                            ->placeholder('Select timezone')
                            ->inlineLabel()
                            ->searchable()
                            ->native(false)
                            ->live(onBlur: true)
                            ->helperText(fn(?string $state) => new HtmlString("<b>Selected: </b>" . Carbon::now($state)->format('M d, Y h:i A T (l)')))
                            ->preload()
                            ->default(config('app.timezone'))
                            ->required(),
                    ]),

                // Contribution Log
                Forms\Components\Section::make('Contribution Log')
                    ->hidden(fn() => empty($this->record))
                    ->columns(['lg' => 4])
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Placeholder::make('Updated By')
                            ->content(fn(): ?string => $this->record?->updater?->name),
                        Forms\Components\Placeholder::make('Updated At')
                            ->content(fn(): ?string => $this->record?->updated_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('Created By')
                            ->content(fn(): ?string => $this->record?->creator?->name),
                        Forms\Components\Placeholder::make('Created At')
                            ->content(fn(): ?string => $this->record?->created_at?->toFormattedDateString()),
                    ]),
            ]);
    }
}
