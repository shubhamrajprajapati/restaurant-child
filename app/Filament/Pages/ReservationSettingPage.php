<?php

namespace App\Filament\Pages;

use App\Models\ReservationSetting;
use App\Traits\FilamentCustomPageAuthorization;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ReservationSettingPage extends Page
{
    use FilamentCustomPageAuthorization;

    protected static ?string $model = ReservationSetting::class;

    protected static string $view = 'filament.pages.reservation-setting-page';

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Customer Engagement';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'reservation-setting';

    protected static ?string $title = 'Reservation Setting';

    public ?ReservationSetting $record;

    public ?array $data = [];

    public function getSubheading(): string
    {
        return __('Configure customer reservation preferences, confirmation messages, and email notifications.');
    }

    public function mount()
    {
        $this->record = ReservationSetting::first();
        $this->data = $this->record?->toArray();
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
                ->authorize(empty($this->record) ? static::canCreate(): static::canEdit($this->record))
                ->formId('reservation_setting')
                ->extraAttributes(['type' => 'submit'])
                ->action('save'),
        ];
    }

    public function save()
    {
        $existingData = ReservationSetting::first();
        $data = $this->form->getState();

        if ($existingData) {
            $data['updated_by_user_id'] = auth()->id();

            $existingData->update($data);

            // Fetch the updated instance
            $this->record = $existingData->fresh();
        } else {
            $data['updated_by_user_id'] = auth()->id();
            $data['created_by_user_id'] = auth()->id();
            $this->record = ReservationSetting::create($data);
        }

        Notification::make('save_record')
            ->title('Success!')
            ->body('Reservation Setting Saved Successfully!')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->model(static::getModel())
            ->schema([

                // Ganeral Settings
                Forms\Components\ToggleButtons::make('active')
                    ->label('Visibility')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->inline()
                    ->default(false)
                    ->boolean()
                    ->required()
                    ->options([
                        1 => 'Show Reservation',
                        0 => 'Hide Reservation',
                    ])
                    ->icons([
                        1 => 'heroicon-m-eye',
                        0 => 'heroicon-m-eye-slash',
                    ]),

                // Customer Information Settings
                Forms\Components\Section::make('Customer Information Settings')
                    ->id('reservation-setting-ask-details')
                    ->icon('heroicon-o-identification')
                    ->description('Configure which customer details are required during reservation.')
                    ->compact()
                    ->collapsible()
                    ->columns(['sm' => 2, 'lg' => 4])
                    ->persistCollapsed()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0'])
                    ->schema([
                        Forms\Components\Toggle::make('ask_name')
                            ->label('Ask for Name')
                            ->required()
                            ->helperText('Enable this to ask the customer for their name during registration.')
                            ->onIcon('heroicon-o-user')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(true)
                            ->disabled(),

                        Forms\Components\Toggle::make('ask_email')
                            ->label('Ask for Email')
                            ->required()
                            ->helperText('Enable this to ask the customer for their email address during registration.')
                            ->onIcon('heroicon-o-at-symbol')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(true)
                            ->disabled(),

                        Forms\Components\Toggle::make('ask_telephone')
                            ->label('Ask for Telephone')
                            ->required()
                            ->helperText('Enable this to ask the customer for their telephone number during registration.')
                            ->onIcon('heroicon-o-phone')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(true)
                            ->disabled(),

                        Forms\Components\Toggle::make('ask_address')
                            ->label('Ask for Address')
                            ->required()
                            ->helperText('Enable this to ask the customer for their address during registration.')
                            ->onIcon('heroicon-o-home')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(false),
                    ]),

                Forms\Components\Section::make('')
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0'])
                    ->schema([
                        Forms\Components\Repeater::make('emails')
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->label('Notification Emails')
                            ->inlineLabel()
                            ->required()
                            ->minItems(1)
                            ->maxItems(5)
                            ->simple(
                                Forms\Components\TextInput::make('email')
                                    ->hiddenLabel()
                                    ->placeholder('Email')
                                    ->required()
                                    ->prefixIcon('heroicon-m-at-symbol')
                                    ->email()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ),
                    ]),

                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0'])
                    ->schema([
                        Forms\Components\RichEditor::make('success_msg')
                            ->inlineLabel()
                            ->required()
                            ->label('Success Message')
                            ->placeholder('Reservation successfully completed.')
                            ->helperText('The message to display to the customer upon successful reservation.')
                            ->columns(3),
                    ]),

                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0'])
                    ->schema([
                        Forms\Components\RichEditor::make('close_msg')
                            ->inlineLabel()
                            ->required()
                            ->label('Close Message')
                            ->placeholder('Reservations are currently closed.')
                            ->helperText('The message to display when reservations are closed.')
                            ->columns(3),
                    ]),

                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0'])
                    ->schema([
                        Forms\Components\RichEditor::make('email_msg')
                            ->inlineLabel()
                            ->required()
                            ->label('Email Message')
                            ->placeholder('Thank you for your reservation.')
                            ->helperText('The message to include in reservation confirmation emails.')
                            ->columns(3),

                    ]),

                // Link Settings
                Forms\Components\Section::make('Link Settings')
                    ->id('reservation-setting-link-setting')
                    ->icon('heroicon-o-link')
                    ->description('Settings for linking with opening hours and email options.')
                    ->compact()
                    ->columns(['md' => 2, 'lg' => 3])
                    ->collapsible()
                    ->persistCollapsed()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->schema([
                        Forms\Components\Toggle::make('link_with_opening_hours')
                            ->label('Link with Opening Hours')
                            ->helperText('Enable to restrict reservations based on restaurant opening hours.')
                            ->onIcon('heroicon-o-clock')
                            ->offIcon('heroicon-o-x-mark')
                            ->disabled()
                            ->default(true),

                        Forms\Components\Toggle::make('mail_to_self')
                            ->label('Send Email to Restaurant')
                            ->helperText('Enable to send reservation details to the restaurant email.')
                            ->onIcon('heroicon-o-building-storefront')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(true),

                        Forms\Components\Toggle::make('mail_to_customer')
                            ->label('Send Email to Customer')
                            ->helperText('Enable to send reservation confirmation emails to customers.')
                            ->onIcon('heroicon-o-user')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(true),
                    ]),

                // Timing Settings
                Forms\Components\Section::make('Timing Settings')
                    ->id('reservation-setting-timing-setting')
                    ->icon('heroicon-o-clock')
                    ->description('Settings for email delays and reservation time intervals.')
                    ->compact()
                    ->collapsible()
                    ->persistCollapsed()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->schema([
                        Forms\Components\TextInput::make('mail_delay')
                            ->inlineLabel()
                            ->label('Reservation Email Delay (in Minutes)')
                            ->placeholder('Enter delay in minutes')
                            ->helperText('Set a delay (in minutes) before sending reservation confirmation emails.')
                            ->prefixIcon('heroicon-o-clock')
                            ->suffix('Minutes')
                            ->default(0)
                            ->required()
                            ->minValue(0),

                        Forms\Components\TextInput::make('time_interval')
                            ->inlineLabel()
                            ->label('Reservation Time Interval (in Minutes)')
                            ->placeholder('Enter interval in minutes')
                            ->helperText('Set the time interval (in minutes) for reservations.')
                            ->prefixIcon('heroicon-o-clock')
                            ->suffix('Minutes')
                            ->default(30)
                            ->required()
                            ->multipleOf(5),
                    ]),

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
