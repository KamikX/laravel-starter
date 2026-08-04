<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use KamikX\LaravelJodit\Filament\Forms\Components\JoditEditor;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('title')
                    ->required()
                    ->columnSpanFull(),
                JoditEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
