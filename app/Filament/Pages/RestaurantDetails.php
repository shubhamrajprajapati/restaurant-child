<?php

namespace App\Filament\Pages;

use App\Models\Restaurant;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class RestaurantDetails extends Page
{
    protected static string $resource = Restaurant::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.restaurant-details';
    protected $record;
    public ?array $data = [];

    public function mount()
    {
        $restaurantDetailFromSuperAdminPanel = app('api.data');

        $this->record = Restaurant::where(['domain' => $restaurantDetailFromSuperAdminPanel['domain']])->first();

        $data = Restaurant::where(['domain' => $restaurantDetailFromSuperAdminPanel['domain']])?->first()?->toArray() ?? $restaurantDetailFromSuperAdminPanel;

        // Convert other_details to array if it's a JSON
        if (isset($data['other_details']) && !is_array($data['other_details'])) {
            $data['other_details'] = json_decode($data['other_details'], true);
        }

        $this->data = $data;
        $this->form->fill($this->data);
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
                ->formId('restaurant_details')
                ->extraAttributes(['type' => 'submit'])
                ->action('save'),
        ];
    }

    public function save()
    {
        $existingRestaurant = Restaurant::where('domain', $this->data['domain'])->first();
        $data = $this->form->getState();

        // Convert other_details to JSON if it's an array
        if (isset($data['other_details']) && is_array($data['other_details'])) {
            $data['other_details'] = json_encode($data['other_details']);
        }

        if ($existingRestaurant) {
            // Update the existing restaurant
            $data['updated_by_user_id'] = auth()->id();
            // Update the existing restaurant
            $existingRestaurant->update($data);

            // Fetch the updated instance
            $this->record = $existingRestaurant->fresh(); // Use fresh() to get the updated model
        } else {
            $data['updated_by_user_id'] = auth()->id();
            $data['created_by_user_id'] = auth()->id();
            $this->record = Restaurant::create($data);
        }

        Notification::make('save_record')
            ->title('Success!')
            ->body('Restaurant Details Saved Successfully!')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->model(Restaurant::class)
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Details')
                            ->columnSpanFull()
                            ->columns(12)
                            ->schema([
                                Forms\Components\Hidden::make('installation_token'),
                                Forms\Components\Fieldset::make('Restaurant Logo')
                                    ->columnSpan(['lg' => 3])
                                    ->schema([
                                        Forms\Components\FileUpload::make('logo')
                                            ->extraAttributes(['class' => 'mx-auto'])
                                            ->disk('public')
                                            ->directory('restaurant-logos')
                                            ->avatar()
                                            ->image()
                                            ->hiddenLabel()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->required()
                                            ->maxSize(1024)
                                            ->downloadable()
                                            ->openable()
                                            ->columnSpanFull(),
                                    ]),
                                Forms\Components\Fieldset::make('Restaurant Name & Domain Url')
                                    ->columns(['lg' => 12])
                                    ->columnSpan(['lg' => 9])
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->hiddenLabel()
                                            ->placeholder('Restaurant Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('domain')
                                            ->hiddenLabel()
                                            ->placeholder('Restaurant Domain Url')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->autosize()
                                    ->columnSpanFull(),
                                Forms\Components\Fieldset::make('Restaurant App Installation Status')
                                    ->hidden()
                                    ->columns(['lg' => 3])
                                    ->columnSpan(['lg' => 12])
                                    ->schema([
                                        Forms\Components\Toggle::make('featured')
                                            ->hidden()
                                            ->required(),
                                        Forms\Components\Toggle::make('visible')
                                            ->hidden()
                                            ->required(),
                                        Forms\Components\Toggle::make('verified')
                                            ->default(false)
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->onIcon('heroicon-o-arrow-down-tray')
                                            ->offIcon('heroicon-o-no-symbol')
                                            ->label('Installed')
                                            ->helperText('Toggle to indicate if the Restaurant App is installed. If the installation is incomplete, switch off to enable the installation button in the system check. This option is intended for debugging and processing manual installations.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Settings')
                            ->schema([
                                Forms\Components\Section::make('Close Restaurant')
                                    ->description('Manage the status and message for temporarily closing your restaurant.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('status')
                                            ->label(fn(Get $get): ?string => $get('status') ? 'Closed Now' : 'Open Now')
                                            ->helperText(
                                                fn(Get $get): ?string => $get('status')
                                                ? 'The service is currently marked as closed. You can also add a custom closing message.'
                                                : 'The restaurant is currently open. You can click the toggle button to mark it as closed.')
                                            ->onIcon('heroicon-o-lock-closed')
                                            ->onColor('danger')
                                            ->offIcon('heroicon-o-lock-open')
                                            ->offColor('success')
                                            ->live(),
                                        Forms\Components\RichEditor::make('status_msg')
                                            ->label('Closure Message')
                                            ->placeholder('Type a message for customers regarding the closure.')
                                            ->visible(fn(Get $get): bool => $get('status')),
                                    ]),
                                Forms\Components\Section::make('Online Order')
                                    ->description('Set the availability of online orders and communicate any changes.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('online_order_status')
                                            ->label(
                                                fn(Get $get): string => $get('online_order_status') ? 'Open for Online Orders' : 'Closed for Online Orders'
                                            )
                                            ->helperText(
                                                fn(Get $get): string => $get('online_order_status')
                                                ? 'The restaurant is open for online orders. Click the toggle button to mark it as closed.'
                                                : 'The restaurant is currently closed for online orders. Toggle on to allow orders again. You can also add a custom closing message.'
                                            )
                                            ->onIcon('heroicon-o-shopping-cart')
                                            ->onColor('success')
                                            ->offIcon('heroicon-o-shopping-cart')
                                            ->offColor('danger')
                                            ->live(),
                                        Forms\Components\RichEditor::make('online_order_msg')
                                            ->label('Order Message')
                                            ->placeholder('Type a message for customers regarding closing online orders.')
                                            ->hidden(fn(Get $get): bool => $get('online_order_status')),
                                    ]),
                                Forms\Components\Section::make('Reservation')
                                    ->description('Control the reservation status and provide necessary information.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('reservation_status')
                                            ->label(
                                                fn(Get $get): string => $get('reservation_status') ? 'Open for Reservations' : 'Closed for Reservations'
                                            )
                                            ->helperText(
                                                fn(Get $get): string => $get('reservation_status')
                                                ? 'The restaurant is open for reservations. Click the toggle button to mark it as closed for new reservations.'
                                                : 'The restaurant is currently closed for reservations. Toggle off to allow reservations again. You can also add a custom closing message.'
                                            )
                                            ->onIcon('heroicon-o-calendar-days')
                                            ->onColor('success')
                                            ->offIcon('heroicon-o-calendar-days')
                                            ->offColor('danger')
                                            ->live(),
                                        Forms\Components\RichEditor::make('reservation_msg')
                                            ->label('Reservation Closing Message')
                                            ->placeholder('Type a message for customers regarding closing reservations.')
                                            ->hidden(fn(Get $get): bool => $get('reservation_status')),
                                    ]),
                                Forms\Components\Section::make('Shutdown')
                                    ->description('Manage the restaurant shutdown process and communicate with customers.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('shutdown_status')
                                            ->label(
                                                fn(Get $get): string => $get('shutdown_status') ? 'Shutdown' : 'Operational'
                                            )
                                            ->helperText(
                                                fn(Get $get): string => $get('shutdown_status')
                                                ? 'The restaurant is currently shut down. Toggle off to resume operations. You can also add a custom shutdown message.'
                                                : 'The restaurant is operational. Click the toggle button to mark it as shut down.'
                                            )
                                            ->onIcon('heroicon-o-power')
                                            ->onColor('danger')
                                            ->offIcon('heroicon-o-check')
                                            ->offColor('success')
                                            ->live(),
                                        Forms\Components\RichEditor::make('shutdown_msg')
                                            ->label('Shutdown Message')
                                            ->placeholder('Type a message for customers regarding the shutdown.')
                                            ->hidden(fn(Get $get): bool => !$get('shutdown_status')),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Other Details')
                            ->schema([
                                Forms\Components\Repeater::make('other_details')
                                    ->hiddenLabel()
                                    ->columnSpanFull()
                                    ->columns(12)
                                    ->collapsible()
                                    ->reorderableWithButtons()
                                    ->schema([
                                        Forms\Components\TextInput::make('key')
                                            ->columnSpan(['lg' => 6]),
                                        Forms\Components\TextInput::make('value')
                                            ->columnSpan(['lg' => 6]),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString(),

                Forms\Components\Fieldset::make('Contribution Log')
                    ->hidden(fn() => empty($this->record))
                    ->columns(['lg' => 4])
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Placeholder::make('Updated By')
                            ->content(fn(): ?string => $this->record?->updater?->name),
                        Forms\Components\Placeholder::make('Updated At')
                            ->content(fn(): ?string => $this->record?->updated_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('Created By')
                            ->content(fn(): ?string => $this->record?->creator?->name),
                        Forms\Components\Placeholder::make('Created At')
                            ->content(fn(): ?string => $this->record?->created_at?->toFormattedDateString() ?? auth()?->user()?->name),
                    ]),

            ]);
    }
}
