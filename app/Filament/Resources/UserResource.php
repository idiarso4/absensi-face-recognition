<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    public static function getModelLabel(): string
    {
        return 'Pengguna';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengguna';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('is_approved')
                                    ->label('Disetujui')
                                    ->default(false),
                                Forms\Components\Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\FileUpload::make('image')
                            ])
                        ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\DateTimePicker::make('email_verified_at'),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create'),
                                
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                        ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(['name', 'email'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Disetujui')
                    ->boolean(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('Status Persetujuan')
                    ->options([
                        '1' => 'Disetujui',
                        '0' => 'Belum Disetujui',
                    ]),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\Filter::make('verified')
                    ->label('Email Terverifikasi')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('unverified')
                    ->label('Email Belum Terverifikasi')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),
                Tables\Filters\Filter::make('created_today')
                    ->label('Dibuat Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
                Tables\Filters\Filter::make('created_this_week')
                    ->label('Dibuat Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
                Tables\Filters\Filter::make('created_this_month')
                    ->label('Dibuat Bulan Ini')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)),
                Tables\Filters\Filter::make('has_image')
                    ->label('Memiliki Foto Profil')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('image')),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_approved)
                    ->action(function ($record) {
                        $record->update(['is_approved' => true]);
                        // Send welcome email
                        $record->notify(new \App\Notifications\WelcomeEmail);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => !$record->is_approved)
                    ->action(fn ($record) => $record->delete()),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Download Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(route('user-template.download'))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('importUsers')
                    ->label('Import Pengguna')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->action(function () {
                        // This will be handled by the form action
                    })
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('File CSV/Excel')
                            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->required()
                            ->directory('imports')
                            ->preserveFilenames(),
                        Forms\Components\Placeholder::make('format_info')
                            ->label('Format File')
                            ->content('File harus memiliki header: name, email, password (opsional), is_approved (opsional). Password default: password123 jika tidak diisi.'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $import = new UsersImport();
                            Excel::import($import, storage_path('app/public/' . $data['file']));

                            $results = $import->getResults();

                            if ($results['error_count'] > 0) {
                                $errorMessages = [];
                                foreach ($results['errors'] as $error) {
                                    $errorMessages[] = 'Baris ' . ($error['row']->get('name') ?? 'Unknown') . ': ' . implode(', ', $error['errors']);
                                }

                                Notification::make()
                                    ->title('Import Selesai dengan Kesalahan')
                                    ->body("Berhasil: {$results['success_count']}, Gagal: {$results['error_count']}\n\nKesalahan:\n" . implode("\n", array_slice($errorMessages, 0, 5)))
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Import Berhasil')
                                    ->body("{$results['success_count']} pengguna berhasil diimpor.")
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import Gagal')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'profile' => Pages\Profile::route('/profile'),
        ];
    }
}
