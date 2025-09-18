<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Absensi';

    protected static ?int $navigationSort = 7;

    public static function getModelLabel(): string
    {
        return 'Absensi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Absensi';
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('schedule_latitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('schedule_longitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('schedule_start_time')
                    ->required(),
                Forms\Components\TextInput::make('schedule_end_time')
                    ->required(),
                Forms\Components\TextInput::make('start_latitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('start_longitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\TextInput::make('end_time')
                    ->required(),
                Forms\Components\TextInput::make('end_latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('end_longitude')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $userId = Auth::user()->id;
                $is_super_admin = Auth::user()->hasRole('super_admin');

                if (!$is_super_admin) {
                    $query->where('attendances.user_id', Auth::user()->id);
                }
                
            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable(['user.name', 'user.email'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_late')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->isLate() ? 'Terlambat' : 'Tepat Waktu';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'danger',
                    })
                    ->description(fn (Attendance $record): string => 'Durasi : '.$record->workDuration()),
               
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu Datang'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Waktu Pulang'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'on_time' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'on_time',
                            fn (Builder $query): Builder => $query->whereRaw('TIME(start_time) <= TIME(schedule_start_time)'),
                        )->when(
                            $data['value'] === 'late',
                            fn (Builder $query): Builder => $query->whereRaw('TIME(start_time) > TIME(schedule_start_time)'),
                        );
                    }),
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
                Tables\Filters\Filter::make('yesterday')
                    ->label('Kemarin')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()->subDay())),
                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
                Tables\Filters\Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)),
                Tables\Filters\Filter::make('has_check_out')
                    ->label('Sudah Check Out')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('end_time')),
                Tables\Filters\Filter::make('no_check_out')
                    ->label('Belum Check Out')
                    ->query(fn (Builder $query): Builder => $query->whereNull('end_time')),
                Tables\Filters\SelectFilter::make('user')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()->hasRole('super_admin')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
