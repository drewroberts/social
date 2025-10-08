<?php

use App\Models\Account;
use App\Models\Purge;

describe('Purge Model', function () {
    it('has correct fillable attributes', function () {
        $fillable = (new Purge)->getFillable();

        expect($fillable)->toContain(
            'post_id',
            'posted_at',
            'text',
            'save',
            'account_id',
            'requested_at',
            'purged_at'
        );
    });

    it('casts dates correctly', function () {
        $casts = (new Purge)->getCasts();

        expect($casts)
            ->toHaveKey('posted_at', 'datetime')
            ->toHaveKey('requested_at', 'datetime')
            ->toHaveKey('purged_at', 'datetime')
            ->toHaveKey('save', 'boolean');
    });

    it('belongs to an account', function () {
        $account = Account::factory()->create();
        $purge = Purge::factory()->create(['account_id' => $account->id]);

        expect($purge->account)
            ->toBeInstanceOf(Account::class)
            ->id->toBe($account->id);
    });

    it('can have null account', function () {
        $purge = Purge::factory()->create(['account_id' => null]);

        expect($purge->account)->toBeNull();
    });
});

describe('Purge Scopes', function () {
    beforeEach(function () {
        $this->pending = Purge::factory()->create([
            'save' => false,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        $this->saved = Purge::factory()->create([
            'save' => true,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        $this->requested = Purge::factory()->create([
            'save' => false,
            'requested_at' => now()->subMinute(),
            'purged_at' => null,
        ]);

        $this->purged = Purge::factory()->create([
            'save' => false,
            'requested_at' => now()->subHour(),
            'purged_at' => now()->subMinutes(30),
        ]);
    });

    it('filters pending purges correctly', function () {
        $pending = Purge::pending()->get();

        expect($pending)
            ->toHaveCount(1)
            ->first()->id->toBe($this->pending->id);
    });

    it('filters saved purges correctly', function () {
        $saved = Purge::where('save', true)->get();

        expect($saved)
            ->toHaveCount(1)
            ->first()->id->toBe($this->saved->id);
    });

    it('filters requested purges correctly', function () {
        $requested = Purge::requested()->get();

        expect($requested)
            ->toHaveCount(1)
            ->first()->id->toBe($this->requested->id);
    });

    it('filters purged records correctly', function () {
        $purged = Purge::purged()->get();

        expect($purged)
            ->toHaveCount(1)
            ->first()->id->toBe($this->purged->id);
    });
});

describe('Purge Status', function () {
    it('returns Pending for new purge', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        expect($purge->status)->toBe('Pending');
    });

    it('returns Saved for saved purge', function () {
        $purge = Purge::factory()->create([
            'save' => true,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        expect($purge->status)->toBe('Saved');
    });

    it('returns Requested for requested purge', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => now(),
            'purged_at' => null,
        ]);

        expect($purge->status)->toBe('Requested');
    });

    it('returns Purged for completed purge', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => now()->subHour(),
            'purged_at' => now(),
        ]);

        expect($purge->status)->toBe('Purged');
    });

    it('prioritizes Saved status over others', function () {
        $purge = Purge::factory()->create([
            'save' => true,
            'requested_at' => now(),
            'purged_at' => now(),
        ]);

        expect($purge->status)->toBe('Saved');
    });
});

describe('Purge Constraints', function () {
    it('enforces unique post_id', function () {
        $postId = fake()->unique()->numerify('##########');
        
        Purge::factory()->create(['post_id' => $postId]);

        expect(fn () => Purge::factory()->create(['post_id' => $postId]))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('allows multiple purges with different post_ids', function () {
        Purge::factory()->count(5)->create();

        expect(Purge::count())->toBe(5);
    });
});
