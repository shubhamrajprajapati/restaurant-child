<?php

namespace App\Filament\Pages;

use App\Models\Restaurant;
use App\Traits\FilamentCustomPageAuthorization;
use DateTime;
use DateTimeZone;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class RestaurantDetails extends Page
{
    use FilamentCustomPageAuthorization;
    protected static string $resource = Restaurant::class;
    protected static ?string $model = Restaurant::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.restaurant-details';
    protected ?Restaurant $record;
    public ?array $data = [];

    public ?string $customTitle = '';

    public function getTitle(): string | Htmlable
    {
        $customTitle = match (Request::query('tab')) {
            '-testimonials-tab' => 'Reviews',
            '-timezone-tab' => 'Timezone',
            '-meta-details-tab' => 'Meta Details',

            default => static::$title ?? (string) str(class_basename(static::class))
                ->kebab()
                ->replace('-', ' ')
                ->title(),
        };

        $this->customTitle = empty($this->customTitle) ? $customTitle : $this->customTitle;

        return $this->customTitle;

    }

    public function mount()
    {
        $restaurantDetailFromSuperAdminPanel = app('api.data');

        $this->record = Restaurant::where(['domain' => $restaurantDetailFromSuperAdminPanel['domain']])?->first();

        $data = Restaurant::where(['domain' => $restaurantDetailFromSuperAdminPanel['domain']])?->first()?->toArray() ?? $restaurantDetailFromSuperAdminPanel;

        // Convert other_details to array if it's a JSON
        if (isset($data['other_details']) && !is_array($data['other_details'])) {
            $data['other_details'] = json_decode($data['other_details'], true);
        }

        // Set default value for timezone
        if (!isset($data['timezone'])) {
            $data['timezone'] = config('app.timezone');
        }

        // Set default value for testimonials
        if (empty($data['testimonials']) || !is_array($data['testimonials'])) {
            $data['testimonials'] = [
                [
                    'status' => false,
                    'reviews' => [
                        [
                            'name' => 'John Doe',
                            'review' => 'Awesome',
                        ],
                    ],
                ],
            ];
        }

        // Set default value for meta details
        if (empty($data['meta_details']) || !is_array($data['meta_details'])) {
            $data['meta_details'] = [
                [
                    'main_page_status' => false,
                    'reviews_page_status' => false,
                    'reservation_page_status' => false,
                    'restaurant_menu_page_status' => false,
                    'takeaway_menu_page_status' => false,
                    'order_online_page_status' => false,
                ],
            ];
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
                ->authorize(empty($this->record) ? static::canCreate(): static::canEdit($this->record))
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
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Restaurant Details')
                            ->icon('heroicon-o-building-storefront')
                            ->columnSpanFull()
                            ->columns(12)
                            ->schema([
                                Forms\Components\Hidden::make('installation_token'),
                                Forms\Components\Section::make('Restaurant Logo Upload')
                                    ->icon('heroicon-o-photo')
                                    ->description('Upload the logo of the restaurant. This will be displayed in the header and other areas of the application.')
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                    ->columnSpan(['lg' => 6])
                                    ->schema([
                                        Forms\Components\FileUpload::make('logo')
                                            ->inlineLabel()
                                            ->label('Restaurant Logo')
                                            ->extraAttributes(['class' => 'mx-auto'])
                                            ->disk('public')
                                            ->directory('logo')
                                            ->avatar()
                                            ->image()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->maxSize(1024)
                                            ->downloadable()
                                            ->openable(),
                                    ]),

                                Forms\Components\Section::make('Restaurant Favicon Upload')
                                    ->icon('heroicon-o-bookmark')
                                    ->description('Upload the favicon of the restaurant. This will appear as the website icon in browser tabs.')
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                    ->columnSpan(['lg' => 6])
                                    ->schema([
                                        Forms\Components\FileUpload::make('favicon')
                                            ->inlineLabel()
                                            ->label('Restaurant Favicon')
                                            ->extraAttributes(['class' => 'mx-auto'])
                                            ->disk('public')
                                            ->directory('favicon')
                                            ->avatar()
                                            ->image()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->maxSize(1024)
                                            ->downloadable()
                                            ->openable(),
                                    ]),

                                Forms\Components\Section::make('')
                                    ->compact()
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                    ->columnSpan(['lg' => 12])
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Restaurant Name')
                                            ->inlineLabel()
                                            ->prefixIcon('heroicon-o-building-storefront')
                                            ->prefixIconColor('primary')
                                            ->placeholder('Restaurant Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\Hidden::make('description'),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Restaurant Description')
                                            ->inlineLabel()
                                            ->required()
                                            ->autosize()
                                            ->columnSpanFull()
                                            ->hidden(),
                                        Forms\Components\Hidden::make('domain'),
                                        Forms\Components\TextInput::make('domain')
                                            ->label('Restaurant Domain')
                                            ->inlineLabel()
                                            ->placeholder('Restaurant Domain Url')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull()
                                            ->hidden(),
                                    ]),
                                Forms\Components\Section::make('')
                                    ->compact()
                                    ->columnSpan(['lg' => 6])
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                    ->schema([
                                        Forms\Components\Repeater::make('emails')
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->columnSpanFull()
                                            ->label('Restaurant Emails')
                                            ->inlineLabel()
                                            ->maxItems(5)
                                            ->simple(
                                                Forms\Components\TextInput::make('email')
                                                    ->hiddenLabel()
                                                    ->placeholder('Email')
                                                    ->required()
                                                    ->prefixIcon('heroicon-m-at-symbol')
                                                    ->prefixIconColor('primary')
                                                    ->email()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),
                                            ),
                                    ]),
                                Forms\Components\Section::make('')
                                    ->compact()
                                    ->columnSpan(['lg' => 6])
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                    ->schema([
                                        Forms\Components\Repeater::make('telephones')
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->columnSpanFull()
                                            ->label('Restaurant Telephones')
                                            ->inlineLabel()
                                            ->maxItems(5)
                                            ->simple(
                                                Forms\Components\TextInput::make('telephone')
                                                    ->hiddenLabel()
                                                    ->placeholder('Phone')
                                                    ->required()
                                                    ->prefixIcon('heroicon-m-phone')
                                                    ->prefixIconColor('primary')
                                                    ->tel()
                                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                                    ->columnSpanFull(),
                                            ),
                                    ]),
                                Forms\Components\Section::make('')
                                    ->compact()
                                    ->columnSpan(['lg' => 12])
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                    ->schema([
                                        Forms\Components\Repeater::make('addresses')
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->columnSpanFull()
                                            ->label('Restaurant Addresses')
                                            ->inlineLabel()
                                            ->maxItems(6)
                                            ->simple(
                                                Forms\Components\TextInput::make('address')
                                                    ->hiddenLabel()
                                                    ->placeholder('Address')
                                                    ->required()
                                                    ->prefixIcon('heroicon-o-map-pin')
                                                    ->prefixIconColor('primary')
                                                    ->columnSpanFull(),
                                            ),
                                    ]),
                                Forms\Components\Section::make('')
                                    ->compact()
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                    ->columnSpan(['lg' => 12])
                                    ->schema([
                                        Forms\Components\TextInput::make('address')
                                            ->label('Geo Location Link')
                                            ->inlineLabel()
                                            ->placeholder('e.g.; https://g.co/kgs/i9TkqjD')
                                            ->prefixIcon('heroicon-m-link')
                                            ->prefixIconColor('primary')
                                            ->columnSpanFull(),
                                    ]),
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
                        Forms\Components\Tabs\Tab::make('Timezone')
                            ->id('timezone')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                Forms\Components\Section::make('')
                                    ->compact()
                                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
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
                                            ->preload()
                                            ->default(config('app.timezone'))
                                            ->required(),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Reviews')
                            ->id('testimonials')
                            ->icon('heroicon-o-star')
                            ->extraAttributes(['class' => '!p-0'])
                            ->schema([
                                Forms\Components\Repeater::make('testimonials')
                                    ->extraAttributes(['class' => '[&>ul>div>li]:!ring-0 [&>ul>div>li]:dark:bg-transparent'])
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->hiddenLabel()
                                    ->schema([
                                        Forms\Components\ToggleButtons::make('status')
                                            ->label('Visibility')
                                            ->hiddenLabel()
                                            ->inline()
                                            ->boolean()
                                            ->options([
                                                1 => 'Show Reviews',
                                                0 => 'Hide Reviews',
                                            ])
                                            ->icons([
                                                1 => 'heroicon-o-eye',
                                                0 => 'heroicon-o-eye-slash',
                                            ]),
                                        Forms\Components\Repeater::make('reviews')
                                            ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                            ->maxItems(10)
                                            ->hiddenLabel()
                                            ->reorderableWithButtons()
                                            ->extraAttributes(['class' => '[&>ul>div>li]:!bg-slate-300/30 [&>ul>div>li]:dark:ring-0 [&>ul>div>li]:dark:!bg-slate-950/30'])
                                            ->collapsible()
                                            ->cloneable()
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Name')
                                                    ->inlineLabel()
                                                    ->prefixIcon('heroicon-m-user')
                                                    ->prefixIconColor('primary')
                                                    ->live(onBlur: true)
                                                    ->required(),
                                                Forms\Components\TextInput::make('review')
                                                    ->label('Review')
                                                    ->inlineLabel()
                                                    ->prefixIcon('heroicon-m-sparkles')
                                                    ->prefixIconColor('primary')
                                                    ->required(),
                                            ]),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Meta Details')
                            ->id('meta-details')
                            ->icon('heroicon-o-code-bracket')
                            ->extraAttributes(['class' => '!p-0'])
                            ->schema([
                                Forms\Components\Repeater::make('meta_details')
                                    ->extraAttributes(['class' => '[&>ul>div>li]:!ring-0 [&>ul>div>li]:dark:bg-transparent'])
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->hiddenLabel()
                                    ->schema([
                                        Forms\Components\Section::make(Str::upper('Main Page'))
                                            ->id('meta_details-main_page')
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('primary')
                                            ->compact()
                                            ->collapsible()
                                            ->persistCollapsed()
                                            ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                            ->columnSpan(['lg' => 12])
                                            ->schema([
                                                Forms\Components\ToggleButtons::make('main_page_status')
                                                    ->label('Visibility')
                                                    ->inlineLabel()
                                                    ->inline()
                                                    ->boolean()
                                                    ->options([
                                                        1 => 'Show',
                                                        0 => 'Hide',
                                                    ])
                                                    ->icons([
                                                        1 => 'heroicon-o-eye',
                                                        0 => 'heroicon-o-eye-slash',
                                                    ]),
                                                Forms\Components\TextInput::make('main_page_title')
                                                    ->label('Title')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta title')
                                                    ->prefixIcon('heroicon-m-h1')
                                                    ->prefixIconColor('primary')
                                                    ->live(onBlur: true)
                                                    ->requiredIfAccepted('main_page_status'),
                                                Forms\Components\TextInput::make('main_page_description')
                                                    ->label('Description')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta description')
                                                    ->prefixIcon('heroicon-m-bars-3-bottom-left')
                                                    ->prefixIconColor('primary')
                                                    ->requiredIfAccepted('main_page_status'),
                                                Forms\Components\TagsInput::make('main_page_keywords')
                                                    ->label('Keywords')
                                                    ->separator(',')
                                                    ->splitKeys(['Tab', ','])
                                                    ->reorderable()
                                                    ->inlineLabel()
                                                    ->placeholder('Enter keywords')
                                                    ->prefixIconColor('primary')
                                                    ->helperText('Use commas (,), the Tab key (→), or the Enter key to separate keywords.')
                                                    ->nestedRecursiveRules([
                                                        'min:3',
                                                        'max:255',
                                                    ]),
                                            ]),
                                        Forms\Components\Section::make(Str::upper('Reviews Page'))
                                            ->id('meta_details-reviews_page')
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('primary')
                                            ->compact()
                                            ->collapsible()
                                            ->persistCollapsed()
                                            ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                            ->columnSpan(['lg' => 12])
                                            ->schema([
                                                Forms\Components\ToggleButtons::make('reviews_page_status')
                                                    ->label('Visibility')
                                                    ->inlineLabel()
                                                    ->inline()
                                                    ->boolean()
                                                    ->options([
                                                        1 => 'Show',
                                                        0 => 'Hide',
                                                    ])
                                                    ->icons([
                                                        1 => 'heroicon-o-eye',
                                                        0 => 'heroicon-o-eye-slash',
                                                    ]),
                                                Forms\Components\TextInput::make('reviews_page_title')
                                                    ->label('Title')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta title')
                                                    ->prefixIcon('heroicon-m-h1')
                                                    ->prefixIconColor('primary')
                                                    ->live(onBlur: true)
                                                    ->requiredIfAccepted('reviews_page_status'),
                                                Forms\Components\TextInput::make('reviews_page_description')
                                                    ->label('Description')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta description')
                                                    ->prefixIcon('heroicon-m-bars-3-bottom-left')
                                                    ->prefixIconColor('primary')
                                                    ->requiredIfAccepted('reviews_page_status'),
                                                Forms\Components\TagsInput::make('reviews_page_keywords')
                                                    ->label('Keywords')
                                                    ->separator(',')
                                                    ->splitKeys(['Tab', ','])
                                                    ->reorderable()
                                                    ->inlineLabel()
                                                    ->placeholder('Enter keywords')
                                                    ->prefixIconColor('primary')
                                                    ->helperText('Use commas (,), the Tab key (→), or the Enter key to separate keywords.')
                                                    ->nestedRecursiveRules([
                                                        'min:3',
                                                        'max:255',
                                                    ]),
                                            ]),
                                        Forms\Components\Section::make(Str::upper('Reservation Page'))
                                            ->id('meta_details-reservation_page')
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('primary')
                                            ->compact()
                                            ->collapsible()
                                            ->persistCollapsed()
                                            ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                            ->columnSpan(['lg' => 12])
                                            ->schema([
                                                Forms\Components\ToggleButtons::make('reservation_page_status')
                                                    ->label('Visibility')
                                                    ->inlineLabel()
                                                    ->inline()
                                                    ->boolean()
                                                    ->options([
                                                        1 => 'Show',
                                                        0 => 'Hide',
                                                    ])
                                                    ->icons([
                                                        1 => 'heroicon-o-eye',
                                                        0 => 'heroicon-o-eye-slash',
                                                    ]),
                                                Forms\Components\TextInput::make('reservation_page_title')
                                                    ->label('Title')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta title')
                                                    ->prefixIcon('heroicon-m-h1')
                                                    ->prefixIconColor('primary')
                                                    ->live(onBlur: true)
                                                    ->requiredIfAccepted('reservation_page_status'),
                                                Forms\Components\TextInput::make('reservation_page_description')
                                                    ->label('Description')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta description')
                                                    ->prefixIcon('heroicon-m-bars-3-bottom-left')
                                                    ->prefixIconColor('primary')
                                                    ->requiredIfAccepted('reservation_page_status'),
                                                Forms\Components\TagsInput::make('reservation_page_keywords')
                                                    ->label('Keywords')
                                                    ->separator(',')
                                                    ->splitKeys(['Tab', ','])
                                                    ->reorderable()
                                                    ->inlineLabel()
                                                    ->placeholder('Enter keywords')
                                                    ->prefixIconColor('primary')
                                                    ->helperText('Use commas (,), the Tab key (→), or the Enter key to separate keywords.')
                                                    ->nestedRecursiveRules([
                                                        'min:3',
                                                        'max:255',
                                                    ]),
                                            ]),
                                        Forms\Components\Section::make(Str::upper('Restaurant Menu Page'))
                                            ->id('meta_details-restaurant_menu_page')
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('primary')
                                            ->compact()
                                            ->collapsible()
                                            ->persistCollapsed()
                                            ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                            ->columnSpan(['lg' => 12])
                                            ->schema([
                                                Forms\Components\ToggleButtons::make('restaurant_menu_page_status')
                                                    ->label('Visibility')
                                                    ->inlineLabel()
                                                    ->inline()
                                                    ->boolean()
                                                    ->options([
                                                        1 => 'Show',
                                                        0 => 'Hide',
                                                    ])
                                                    ->icons([
                                                        1 => 'heroicon-o-eye',
                                                        0 => 'heroicon-o-eye-slash',
                                                    ]),
                                                Forms\Components\TextInput::make('restaurant_menu_page_title')
                                                    ->label('Title')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta title')
                                                    ->prefixIcon('heroicon-m-h1')
                                                    ->prefixIconColor('primary')
                                                    ->live(onBlur: true)
                                                    ->requiredIfAccepted('restaurant_menu_page_status'),
                                                Forms\Components\TextInput::make('restaurant_menu_page_description')
                                                    ->label('Description')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta description')
                                                    ->prefixIcon('heroicon-m-bars-3-bottom-left')
                                                    ->prefixIconColor('primary')
                                                    ->requiredIfAccepted('restaurant_menu_page_status'),
                                                Forms\Components\TagsInput::make('restaurant_menu_page_keywords')
                                                    ->label('Keywords')
                                                    ->separator(',')
                                                    ->splitKeys(['Tab', ','])
                                                    ->reorderable()
                                                    ->inlineLabel()
                                                    ->placeholder('Enter keywords')
                                                    ->prefixIconColor('primary')
                                                    ->helperText('Use commas (,), the Tab key (→), or the Enter key to separate keywords.')
                                                    ->nestedRecursiveRules([
                                                        'min:3',
                                                        'max:255',
                                                    ]),
                                            ]),
                                        Forms\Components\Section::make(Str::upper('Takeaway Menu Page'))
                                            ->id('meta_details-takeaway_menu_page')
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('primary')
                                            ->compact()
                                            ->collapsible()
                                            ->persistCollapsed()
                                            ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                            ->columnSpan(['lg' => 12])
                                            ->schema([
                                                Forms\Components\ToggleButtons::make('takeaway_menu_page_status')
                                                    ->label('Visibility')
                                                    ->inlineLabel()
                                                    ->inline()
                                                    ->boolean()
                                                    ->options([
                                                        1 => 'Show',
                                                        0 => 'Hide',
                                                    ])
                                                    ->icons([
                                                        1 => 'heroicon-o-eye',
                                                        0 => 'heroicon-o-eye-slash',
                                                    ]),
                                                Forms\Components\TextInput::make('takeaway_menu_page_title')
                                                    ->label('Title')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta title')
                                                    ->prefixIcon('heroicon-m-h1')
                                                    ->prefixIconColor('primary')
                                                    ->live(onBlur: true)
                                                    ->requiredIfAccepted('takeaway_menu_page_status'),
                                                Forms\Components\TextInput::make('takeaway_menu_page_description')
                                                    ->label('Description')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta description')
                                                    ->prefixIcon('heroicon-m-bars-3-bottom-left')
                                                    ->prefixIconColor('primary')
                                                    ->requiredIfAccepted('takeaway_menu_page_status'),
                                                Forms\Components\TagsInput::make('takeaway_menu_page_keywords')
                                                    ->label('Keywords')
                                                    ->separator(',')
                                                    ->splitKeys(['Tab', ','])
                                                    ->reorderable()
                                                    ->inlineLabel()
                                                    ->placeholder('Enter keywords')
                                                    ->prefixIconColor('primary')
                                                    ->helperText('Use commas (,), the Tab key (→), or the Enter key to separate keywords.')
                                                    ->nestedRecursiveRules([
                                                        'min:3',
                                                        'max:255',
                                                    ]),
                                            ]),
                                        Forms\Components\Section::make(Str::upper('Order Online Page'))
                                            ->id('meta_details-order_online_page')
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('primary')
                                            ->compact()
                                            ->collapsible()
                                            ->persistCollapsed()
                                            ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-950/30 ring-0 dark:ring-0'])
                                            ->columnSpan(['lg' => 12])
                                            ->schema([
                                                Forms\Components\ToggleButtons::make('order_online_page_status')
                                                    ->label('Visibility')
                                                    ->inlineLabel()
                                                    ->inline()
                                                    ->boolean()
                                                    ->options([
                                                        1 => 'Show',
                                                        0 => 'Hide',
                                                    ])
                                                    ->icons([
                                                        1 => 'heroicon-o-eye',
                                                        0 => 'heroicon-o-eye-slash',
                                                    ]),
                                                Forms\Components\TextInput::make('order_online_page_title')
                                                    ->label('Title')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta title')
                                                    ->prefixIcon('heroicon-m-h1')
                                                    ->prefixIconColor('primary')
                                                    ->live(onBlur: true)
                                                    ->requiredIfAccepted('order_online_page_status'),
                                                Forms\Components\TextInput::make('order_online_page_description')
                                                    ->label('Description')
                                                    ->inlineLabel()
                                                    ->placeholder('Enter meta description')
                                                    ->prefixIcon('heroicon-m-bars-3-bottom-left')
                                                    ->prefixIconColor('primary')
                                                    ->requiredIfAccepted('order_online_page_status'),
                                                Forms\Components\TagsInput::make('order_online_page_keywords')
                                                    ->label('Keywords')
                                                    ->separator(',')
                                                    ->splitKeys(['Tab', ','])
                                                    ->reorderable()
                                                    ->inlineLabel()
                                                    ->placeholder('Enter keywords')
                                                    ->prefixIconColor('primary')
                                                    ->helperText('Use commas (,), the Tab key (→), or the Enter key to separate keywords.')
                                                    ->nestedRecursiveRules([
                                                        'min:3',
                                                        'max:255',
                                                    ]),
                                            ]),
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
