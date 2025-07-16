<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Jobs\ProcessUserImportJob;
use App\Models\School;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class UserImport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.user-import';

    // public $import_file;

    // public $role;

    // public $school_id;

    public ?array $data = [];

    public function mount(): void {}

    public function getFormSchema(): array
    {
        return [
            FileUpload::make('import_file')
                ->label('Upload CSV')
                ->disk('local')
                ->directory('imports')
                ->required()
                ->acceptedFileTypes(['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),

            Select::make('role')
                ->label('Role')
                ->options([
                    'student' => 'Student',
                    'teacher' => 'Teacher',
                ])
                ->required(),

            Select::make('school_id')
                ->label('School')
                ->options(School::pluck('name', 'id')->toArray())
                ->required(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();

        // dd($data);

        //  \Log::info('Submitted form data:', $data);

        $path = $data['import_file'];
        $role = $data['role'];
        $schoolId = $data['school_id'];

        ProcessUserImportJob::dispatch($path, $role, $schoolId);

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Import Started')
            ->body('Your import is queued. Users will be invited shortly.')
            ->send();

        $this->redirect(UserResource::getUrl());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
