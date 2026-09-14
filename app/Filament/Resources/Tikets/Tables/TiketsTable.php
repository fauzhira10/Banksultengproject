<?php

namespace App\Filament\Resources\Tikets\Tables;

use App\Filament\Resources\Tikets\TiketResource;
use App\Models\Cabang;
use App\Models\Tiket;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TiketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_tiket')
                    ->label('No Tiket')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('permasalahan')
                    ->label('Permasalahan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('medium'),

                TextColumn::make('kategori_problem')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Mesin ATM' => 'primary',
                        'System' => 'info',
                        'Jaringan' => 'warning',
                        'Listrik' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->state(fn (Tiket $record): string => $record->lokasi ?? $record->terminal?->nama_lokasi ?? '-')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('atm_id')
                    ->label('ID / LUNO')
                    ->state(fn (Tiket $record): string => $record->atm_id ?? $record->terminal?->luno ?? '-')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('profil')
                    ->label('Profile')
                    ->state(fn (Tiket $record): string => $record->profil ?? $record->terminal?->profil ?? '-')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipe_mesin')
                    ->label('Type')
                    ->state(fn (Tiket $record): string => $record->tipe_mesin ?? $record->terminal?->tipe_mesin ?? '-')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('serial_number')
                    ->label('SN')
                    ->state(fn (Tiket $record): string => $record->serial_number ?? $record->terminal?->serial_number ?? '-')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('mulai')
                    ->label('Open Tiket')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('selesai')
                    ->label('Closed Tiket')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Masih Open')
                    ->color(fn ($state) => $state ? null : 'warning'),

                TextColumn::make('durasi_lengkap')
                    ->label('Durasi Lengkap')
                    ->placeholder('Masih Berjalan')
                    ->description(fn (Tiket $record): string => $record->durasi_jam_menit ? "{$record->durasi_jam_menit} ({$record->durasi_menit} mnt)" : '')
                    ->toggleable(),

                TextColumn::make('durasi_jam_menit')
                    ->label('Jam:Menit')
                    ->placeholder('-')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('durasi_menit')
                    ->label('Total Menit')
                    ->numeric()
                    ->placeholder('-')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Open' => 'warning',
                        'Closed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('mulai', 'desc')
            ->recordClasses(fn (Tiket $record): ?string => $record->status === 'Open' ? 'bs-tiket-row-open' : null)
            ->recordUrl(fn (Tiket $record): string => TiketResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Tiket')
                    ->options([
                        'Open' => 'Open (Dalam Proses)',
                        'Closed' => 'Closed (Selesai)',
                    ]),

                SelectFilter::make('kategori_problem')
                    ->label('Kategori Problem')
                    ->options([
                        'Mesin ATM' => 'Mesin ATM',
                        'System' => 'System',
                        'Jaringan' => 'Jaringan',
                        'Listrik' => 'Listrik',
                    ]),

                SelectFilter::make('cabang_id')
                    ->label('Kantor Cabang')
                    ->options(fn () => Cabang::orderBy('urutan')->pluck('label_cabang', 'id')->toArray())
                    ->searchable(),

                Filter::make('rentang_waktu')
                    ->form([
                        DateTimePicker::make('dari')->label('Mulai Dari'),
                        DateTimePicker::make('sampai')->label('Sampai Dengan'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('mulai', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('mulai', '<=', $date),
                            );
                    }),

                TrashedFilter::make(),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActionsAlignment('center')
            ->actions([
                Action::make('closeTicket')
                    ->label('Tutup Tiket')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->size(Size::ExtraSmall)
                    ->disabled(fn (Tiket $record): bool => $record->status !== 'Open')
                    ->extraAttributes(fn (Tiket $record): array => $record->status === 'Open' ? [] : [
                        'class' => 'invisible pointer-events-none select-none',
                        'tabindex' => '-1',
                        'aria-hidden' => 'true',
                    ])
                    ->form([
                        DateTimePicker::make('selesai')
                            ->label('Waktu Selesai (Closed)')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (Tiket $record, array $data): void {
                        if ($record->status !== 'Open') {
                            return;
                        }

                        $record->selesai = $data['selesai'];
                        $record->status = 'Closed';
                        $record->save();
                    }),

                EditAction::make()
                    ->label('Ubah')
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary')
                    ->button()
                    ->size(Size::ExtraSmall),

                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->button()
                    ->size(Size::ExtraSmall),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
