<?php

use App\Models\Account;
use App\Models\Purge;
use App\Models\User;

describe('PurgeBatchSave Command', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create(['user_id' => $this->user->id]);
    });

    it('marks purges as saved when text matches', function () {
        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This is an important tweet',
            'save' => false,
        ]);

        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This is another important message',
            'save' => false,
        ]);

        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This is not relevant',
            'save' => false,
        ]);

        $this->artisan('purge:batchsave', ['text' => 'important'])
            ->expectsOutput('Searching for purges containing: "important" (case-insensitive)')
            ->assertSuccessful();

        expect(Purge::where('text', 'like', '%important%')->where('save', true)->count())->toBe(2);
        expect(Purge::where('text', 'not like', '%important%')->where('save', false)->count())->toBe(1);
    });

    it('only updates unsaved purges', function () {
        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'Already saved important tweet',
            'save' => true,
        ]);

        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'Not saved important tweet',
            'save' => false,
        ]);

        $this->artisan('purge:batchsave', ['text' => 'important'])
            ->assertSuccessful();

        expect(Purge::where('save', true)->count())->toBe(2);
    });

    it('performs case-sensitive search when option is provided', function () {
        $uppercase = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains IMPORTANT in caps',
            'save' => false,
        ]);

        $lowercase = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains important in lowercase',
            'save' => false,
        ]);

        $this->artisan('purge:batchsave', ['text' => 'IMPORTANT', '--case-sensitive' => true])
            ->expectsOutput('Searching for purges containing: "IMPORTANT" (case-sensitive)')
            ->assertSuccessful();

        expect($uppercase->fresh()->save)->toBeTrue();
        expect($lowercase->fresh()->save)->toBeFalse();
    });

    it('performs regex search when option is provided', function () {
        $withNumbers1 = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains urgent123 with numbers',
            'save' => false,
        ]);

        $withNumbers2 = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains urgent456',
            'save' => false,
        ]);

        $withoutNumbers = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains urgent without numbers',
            'save' => false,
        ]);

        $this->artisan('purge:batchsave', ['text' => 'urgent[0-9]+', '--regex' => true])
            ->expectsOutput('Searching for purges containing: "urgent[0-9]+" (regex)')
            ->assertSuccessful();

        expect($withNumbers1->fresh()->save)->toBeTrue();
        expect($withNumbers2->fresh()->save)->toBeTrue();
        expect($withoutNumbers->fresh()->save)->toBeFalse();
    });

    it('shows warning when no purges match', function () {
        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This does not match',
            'save' => false,
        ]);

        $this->artisan('purge:batchsave', ['text' => 'nonexistent'])
            ->expectsOutput('No purges found matching the search criteria.')
            ->assertSuccessful();

        expect(Purge::where('save', true)->count())->toBe(0);
    });

    it('displays progress bar and success message', function () {
        Purge::factory()->count(5)->create([
            'account_id' => $this->account->id,
            'text' => 'Important message',
            'save' => false,
        ]);

        $this->artisan('purge:batchsave', ['text' => 'Important'])
            ->expectsOutput('Found 5 purge(s) to mark as saved.')
            ->expectsOutput('Successfully marked 5 purge(s) as saved.')
            ->assertSuccessful();
    });
});

describe('PurgeBatchUnsave Command', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create(['user_id' => $this->user->id]);
    });

    it('marks purges as unsaved when text matches', function () {
        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This is an important tweet',
            'save' => true,
        ]);

        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This is another important message',
            'save' => true,
        ]);

        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This is not relevant',
            'save' => true,
        ]);

        $this->artisan('purge:batchunsave', ['text' => 'important'])
            ->expectsOutput('Searching for purges containing: "important" (case-insensitive)')
            ->assertSuccessful();

        expect(Purge::where('text', 'like', '%important%')->where('save', false)->count())->toBe(2);
        expect(Purge::where('text', 'not like', '%important%')->where('save', true)->count())->toBe(1);
    });

    it('only updates saved purges', function () {
        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'Already unsaved important tweet',
            'save' => false,
        ]);

        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'Saved important tweet',
            'save' => true,
        ]);

        $this->artisan('purge:batchunsave', ['text' => 'important'])
            ->assertSuccessful();

        expect(Purge::where('save', false)->count())->toBe(2);
    });

    it('performs case-sensitive search when option is provided', function () {
        $uppercase = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains IMPORTANT in caps',
            'save' => true,
        ]);

        $lowercase = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains important in lowercase',
            'save' => true,
        ]);

        $this->artisan('purge:batchunsave', ['text' => 'IMPORTANT', '--case-sensitive' => true])
            ->expectsOutput('Searching for purges containing: "IMPORTANT" (case-sensitive)')
            ->assertSuccessful();

        expect($uppercase->fresh()->save)->toBeFalse();
        expect($lowercase->fresh()->save)->toBeTrue();
    });

    it('performs regex search when option is provided', function () {
        $withNumbers1 = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains urgent123 with numbers',
            'save' => true,
        ]);

        $withNumbers2 = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains urgent456',
            'save' => true,
        ]);

        $withoutNumbers = Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This contains urgent without numbers',
            'save' => true,
        ]);

        $this->artisan('purge:batchunsave', ['text' => 'urgent[0-9]+', '--regex' => true])
            ->expectsOutput('Searching for purges containing: "urgent[0-9]+" (regex)')
            ->assertSuccessful();

        expect($withNumbers1->fresh()->save)->toBeFalse();
        expect($withNumbers2->fresh()->save)->toBeFalse();
        expect($withoutNumbers->fresh()->save)->toBeTrue();
    });

    it('shows warning when no purges match', function () {
        Purge::factory()->create([
            'account_id' => $this->account->id,
            'text' => 'This does not match',
            'save' => true,
        ]);

        $this->artisan('purge:batchunsave', ['text' => 'nonexistent'])
            ->expectsOutput('No purges found matching the search criteria.')
            ->assertSuccessful();

        expect(Purge::where('save', false)->count())->toBe(0);
    });

    it('displays progress bar and success message', function () {
        Purge::factory()->count(5)->create([
            'account_id' => $this->account->id,
            'text' => 'Important message',
            'save' => true,
        ]);

        $this->artisan('purge:batchunsave', ['text' => 'Important'])
            ->expectsOutput('Found 5 purge(s) to mark as unsaved.')
            ->expectsOutput('Successfully marked 5 purge(s) as unsaved.')
            ->assertSuccessful();
    });
});
