<?php

use App\Filament\Resources\Posts\PostResource;
use App\Models\User;
use Filament\Facades\Filament;

test('an authenticated user can see and access the posts resource', function () {
    $this->actingAs(User::factory()->create());

    Filament::setCurrentPanel('admin');

    expect(PostResource::canAccess())->toBeTrue()
        ->and(PostResource::getNavigationItems())->not->toBeEmpty();
});
