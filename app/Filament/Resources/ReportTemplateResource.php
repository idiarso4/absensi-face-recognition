<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportTemplateResource\Pages;
use App\Filament\Resources\ReportTemplateResource\RelationManagers;
use App\Models\ReportTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class ReportTemplateResource extends Resource
{
    protected static ?string $model = ReportTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Laporan & Analitik';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return 'Template Laporan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Template Laporan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Template')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Template')
                            ->rules(['required', 'string', 'max:255']),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'attendance' => 'Laporan Absensi',
                                'leave' => 'Laporan Cuti/Izin',
                                'monthly-summary' => 'Ringkasan Bulanan',
                            ])
                            ->label('Jenis Laporan')
                            ->rules(['required', 'in:attendance,leave,monthly-summary']),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->label('Deskripsi')
                            ->rules(['nullable', 'string', 'max:1000'])
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Konfigurasi Filter')
                    ->schema([
                        Forms\Components\KeyValue::make('filters')
                            ->label('Filter Laporan')
                            ->keyLabel('Parameter')
                            ->valueLabel('Nilai')
                            ->rules(['nullable', 'array'])
                            ->helperText('Tambahkan filter seperti user_id, start_date, end_date, dll.'),
                    ]),

                Forms\Components\Section::make('Pengaturan Email')
                    ->schema([
                        Forms\Components\Repeater::make('recipients')
                            ->label('Penerima Email')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->label('Email')
                                    ->rules(['required', 'email:rfc,dns']),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->rules(['nullable', 'string', 'max:255'])
                                    ->placeholder('Opsional'),
                            ])
                            ->columns(2)
                            ->rules(['nullable', 'array'])
                            ->collapsible(),
                    ]),

                Forms\Components\Section::make('Jadwal Otomatis')
                    ->schema([
                        Forms\Components\Select::make('schedule_frequency')
                            ->options([
                                'manual' => 'Manual',
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                            ])
                            ->default('manual')
                            ->live()
                            ->label('Frekuensi')
                            ->rules(['required', 'in:manual,daily,weekly,monthly']),

                        Forms\Components\Select::make('schedule_day')
                            ->options([
                                'monday' => 'Senin',
                                'tuesday' => 'Selasa',
                                'wednesday' => 'Rabu',
                                'thursday' => 'Kamis',
                                'friday' => 'Jumat',
                                'saturday' => 'Sabtu',
                                'sunday' => 'Minggu',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('schedule_frequency') === 'weekly')
                            ->required(fn (Forms\Get $get) => $get('schedule_frequency') === 'weekly')
                            ->label('Hari')
                            ->rules([
                                'required_if:schedule_frequency,weekly',
                                'nullable',
                                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
                            ]),

                        Forms\Components\TextInput::make('schedule_time')
                            ->type('time')
                            ->default('08:00')
                            ->visible(fn (Forms\Get $get) => $get('schedule_frequency') !== 'manual')
                            ->required(fn (Forms\Get $get) => $get('schedule_frequency') !== 'manual')
                            ->label('Waktu')
                            ->rules([
                                'required_if:schedule_frequency,daily,weekly,monthly',
                                'nullable',
                                'date_format:H:i'
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Aktif')
                            ->rules(['boolean']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nama'),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'attendance',
                        'success' => 'leave',
                        'warning' => 'monthly-summary',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'attendance' => 'Absensi',
                        'leave' => 'Cuti',
                        'monthly-summary' => 'Ringkasan',
                        default => $state,
                    })
                    ->label('Jenis'),

                Tables\Columns\TextColumn::make('schedule_frequency')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'manual' => 'gray',
                        'daily' => 'info',
                        'weekly' => 'warning',
                        'monthly' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'manual' => 'Manual',
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                        default => $state,
                    })
                    ->label('Jadwal'),

                Tables\Columns\TextColumn::make('schedule_time')
                    ->label('Waktu'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('last_generated_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Terakhir Generate')
                    ->placeholder('Belum pernah'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->label('Dibuat')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'attendance' => 'Laporan Absensi',
                        'leave' => 'Laporan Cuti/Izin',
                        'monthly-summary' => 'Ringkasan Bulanan',
                    ])
                    ->label('Jenis Laporan'),

                Tables\Filters\SelectFilter::make('schedule_frequency')
                    ->options([
                        'manual' => 'Manual',
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                    ])
                    ->label('Frekuensi'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_templates')
                    ->label('Export Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $templates = ReportTemplate::all();
                        $data = $templates->map(function ($template) {
                            return [
                                'name' => $template->name,
                                'type' => $template->type,
                                'description' => $template->description,
                                'filters' => $template->filters,
                                'recipients' => $template->recipients,
                                'schedule_frequency' => $template->schedule_frequency,
                                'schedule_day' => $template->schedule_day,
                                'schedule_time' => $template->schedule_time,
                                'is_active' => $template->is_active,
                            ];
                        });

                        $filename = 'report-templates-' . now()->format('Y-m-d-H-i-s') . '.json';
                        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

                        return response()->streamDownload(function () use ($jsonContent) {
                            echo $jsonContent;
                        }, $filename, ['Content-Type' => 'application/json']);
                    }),

                Tables\Actions\Action::make('import_templates')
                    ->label('Import Template')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray')
                    ->form([
                        Forms\Components\FileUpload::make('template_file')
                            ->label('File Template')
                            ->acceptedFileTypes(['application/json'])
                            ->required()
                            ->directory('temp')
                            ->helperText('Upload file JSON yang diekspor dari template laporan'),
                    ])
                    ->action(function (array $data) {
                        $file = $data['template_file'];
                        $path = storage_path('app/public/temp/' . $file);
                        $content = file_get_contents($path);
                        $templates = json_decode($content, true);

                        $imported = 0;
                        foreach ($templates as $templateData) {
                            try {
                                ReportTemplate::create($templateData);
                                $imported++;
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Failed to import template: {$e->getMessage()}");
                            }
                        }

                        // Clean up uploaded file
                        if (file_exists($path)) {
                            unlink($path);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Import selesai')
                            ->body("{$imported} template berhasil diimpor")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('generate')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (ReportTemplate $record) {
                        try {
                            $pdfContent = $record->generateReport();
                            $filename = 'template-' . $record->id . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';

                            // Save to storage
                            \Illuminate\Support\Facades\Storage::disk('local')->put('reports/' . $filename, $pdfContent);

                            // Send to recipients if any
                            if (!empty($record->recipients)) {
                                $record->sendToRecipients($pdfContent, $filename);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Laporan berhasil dihasilkan')
                                ->body("File: {$filename}")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal menghasilkan laporan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->label('Generate'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('generate_selected')
                        ->label('Generate Laporan Terpilih')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $generated = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                try {
                                    $pdfContent = $record->generateReport();
                                    $filename = 'template-' . $record->id . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';

                                    // Save to storage
                                    \Illuminate\Support\Facades\Storage::disk('local')->put('reports/' . $filename, $pdfContent);

                                    // Send to recipients if any
                                    if (!empty($record->recipients)) {
                                        $record->sendToRecipients($pdfContent, $filename);
                                    }

                                    $generated++;
                                } catch (\Exception $e) {
                                    $failed++;
                                    \Illuminate\Support\Facades\Log::error("Failed to generate report for template {$record->id}: {$e->getMessage()}");
                                }
                            }

                            $message = "Berhasil generate {$generated} laporan";
                            if ($failed > 0) {
                                $message .= ", gagal {$failed}";
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Generate Laporan Selesai')
                                ->body($message)
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportTemplates::route('/'),
            'create' => Pages\CreateReportTemplate::route('/create'),
            'edit' => Pages\EditReportTemplate::route('/{record}/edit'),
        ];
    }
}
