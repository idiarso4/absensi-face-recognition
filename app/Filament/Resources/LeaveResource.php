<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Filament\Resources\LeaveResource\RelationManagers;
use App\Models\Leave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Auth;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-m-minus-circle';

    protected static ?string $navigationGroup = 'Manajemen Absensi';
    protected static ?int $navigationSort = 8;

    public static function getModelLabel(): string
    {
        return 'Cuti';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cuti';
    }

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\Section::make('Detail')
                ->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->required(),
                    Forms\Components\DatePicker::make('end_date')
                        ->required(),
                    Forms\Components\Textarea::make('reason')
                        ->columnSpanFull(),
                ]),
        ];
    
        if (Auth::user()->hasRole('super_admin')) {
            $schema[] = Forms\Components\Section::make('Approval')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->required()
                        ->label('Status'),
                    Forms\Components\Textarea::make('note')
                        ->label('Note')
                        ->columnSpanFull()
                ]);
        }
    
        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $is_super_admin = Auth::user()->hasRole('super_admin');

                if (!$is_super_admin) {
                    $query->where('user_id', Auth::user()->id);
                }
            })
            ->columns([
                
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(['user.name', 'user.email'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger'
                    })
                    ->description(fn (Leave $record): ?string => $record->note ?? null )
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('start_date', now()->month)
                        ->whereYear('start_date', now()->year)),
                Tables\Filters\Filter::make('next_month')
                    ->label('Bulan Depan')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('start_date', now()->addMonth()->month)
                        ->whereYear('start_date', now()->addMonth()->year)),
                Tables\Filters\Filter::make('current_year')
                    ->label('Tahun Ini')
                    ->query(fn (Builder $query): Builder => $query->whereYear('start_date', now()->year)),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Akan Datang')
                    ->query(fn (Builder $query): Builder => $query->where('start_date', '>=', today())),
                Tables\Filters\Filter::make('past')
                    ->label('Sudah Lewat')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<', today())),
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
