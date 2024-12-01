<?php

namespace App\Filament\Pages;

use App\Models\OpeningHour;
use App\Traits\FilamentCustomPageAuthorization;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OpeningHourPage extends Page
{
    use FilamentCustomPageAuthorization;

    protected static ?string $model = OpeningHour::class;

    protected static string $view = 'filament.pages.opening-hour-page';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Restaurant Information';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'opening-hour';

    protected static ?string $title = 'Opening Hour';

    public ?OpeningHour $record;

    public ?array $data = [];

    public function getSubheading(): string
    {
        return __('Set and update the restaurant\'s operational hours for each day of the week.');
    }

    public function mount()
    {
        $this->record = OpeningHour::first();
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
                ->formId('opening_hour')
                ->extraAttributes(['type' => 'submit'])
                ->action('save'),
        ];
    }

    public function save()
    {
        $existingData = OpeningHour::first();
        $data = $this->form->getState();

        if ($existingData) {
            $data['updated_by_user_id'] = auth()->id();

            $existingData->update($data);

            // Fetch the updated instance
            $this->record = $existingData->fresh();
        } else {
            $data['updated_by_user_id'] = auth()->id();
            $data['created_by_user_id'] = auth()->id();
            $this->record = OpeningHour::create($data);
        }

        Notification::make('save_record')
            ->title('Success!')
            ->body('Opening Hour Saved Successfully!')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->model(static::getModel())
            ->schema([
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->columns(['sm' => 2, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Toggle::make('monday_open')
                                    ->label('Active')
                                    ->hiddenLabel()
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\Placeholder::make('monday_name')
                                    ->label('Monday'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('monday_start_time_1')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('monday_open'),
                                Forms\Components\TimePicker::make('monday_end_time_1')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('monday_open'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('monday_start_time_2')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('monday_open'),
                                Forms\Components\TimePicker::make('monday_end_time_2')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('monday_open'),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->columns(['sm' => 2, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Toggle::make('tuesday_open')
                                    ->label('Active')
                                    ->hiddenLabel()
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\Placeholder::make('tuesday_name')
                                    ->label('Tuesday'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('tuesday_start_time_1')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('tuesday_open'),
                                Forms\Components\TimePicker::make('tuesday_end_time_1')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('tuesday_open'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('tuesday_start_time_2')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('tuesday_open'),
                                Forms\Components\TimePicker::make('tuesday_end_time_2')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('tuesday_open'),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->columns(['sm' => 2, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Toggle::make('wednesday_open')
                                    ->label('Active')
                                    ->hiddenLabel()
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\Placeholder::make('wednesday_name')
                                    ->label('Wednesday'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('wednesday_start_time_1')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('wednesday_open'),
                                Forms\Components\TimePicker::make('wednesday_end_time_1')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('wednesday_open'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('wednesday_start_time_2')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('wednesday_open'),
                                Forms\Components\TimePicker::make('wednesday_end_time_2')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('wednesday_open'),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->columns(['sm' => 2, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Toggle::make('thursday_open')
                                    ->label('Active')
                                    ->hiddenLabel()
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\Placeholder::make('thursday_name')
                                    ->label('Thursday'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('thursday_start_time_1')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('thursday_open'),
                                Forms\Components\TimePicker::make('thursday_end_time_1')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('thursday_open'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('thursday_start_time_2')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('thursday_open'),
                                Forms\Components\TimePicker::make('thursday_end_time_2')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('thursday_open'),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->columns(['sm' => 2, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Toggle::make('friday_open')
                                    ->label('Active')
                                    ->hiddenLabel()
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\Placeholder::make('friday_name')
                                    ->label('Friday'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('friday_start_time_1')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('friday_open'),
                                Forms\Components\TimePicker::make('friday_end_time_1')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('friday_open'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('friday_start_time_2')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('friday_open'),
                                Forms\Components\TimePicker::make('friday_end_time_2')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('friday_open'),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->columns(['sm' => 2, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Toggle::make('saturday_open')
                                    ->label('Active')
                                    ->hiddenLabel()
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\Placeholder::make('saturday_name')
                                    ->label('Saturday'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('saturday_start_time_1')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('saturday_open'),
                                Forms\Components\TimePicker::make('saturday_end_time_1')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('saturday_open'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('saturday_start_time_2')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('saturday_open'),
                                Forms\Components\TimePicker::make('saturday_end_time_2')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('saturday_open'),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0 [&>div>div>div]:items-center'])
                    ->columns(['sm' => 2, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Toggle::make('sunday_open')
                                    ->label('Active')
                                    ->hiddenLabel()
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\Placeholder::make('sunday_name')
                                    ->label('Sunday'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('sunday_start_time_1')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('sunday_open'),
                                Forms\Components\TimePicker::make('sunday_end_time_1')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('sunday_open'),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['lg' => 2])
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\TimePicker::make('sunday_start_time_2')
                                    ->label('Start Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('success')
                                    ->requiredIfAccepted('sunday_open'),
                                Forms\Components\TimePicker::make('sunday_end_time_2')
                                    ->label('End Time')
                                    ->hiddenLabel()
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->placeholder('Start Time')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->prefixIconColor('danger')
                                    ->requiredIfAccepted('sunday_open'),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->compact()
                    ->extraAttributes(['class' => '!bg-slate-300/30 dark:!bg-slate-800/30 ring-0 dark:ring-0'])
                    ->schema([
                        Forms\Components\RichEditor::make('message')
                            ->inlineLabel()
                            ->label('Timings Text')
                            ->placeholder('Opening hour message.')
                            ->helperText('The message to display as restaurant opening hour.')
                            ->columns(3),
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
