<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\JournalEntryResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\JournalEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * bug_rule_004 : CreateAction visible() garantit que canCreate() est respecté.
 */
class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn (): bool => JournalEntryResource::canCreate()),
        ];
    }
}
