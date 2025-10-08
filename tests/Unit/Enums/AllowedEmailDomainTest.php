<?php

use App\Enums\AllowedEmailDomain;

describe('AllowedEmailDomain Enum', function () {
    it('contains drewroberts.com as an allowed domain', function () {
        expect(AllowedEmailDomain::DREWROBERTS->value)->toBe('drewroberts.com');
    });

    it('returns all allowed domains', function () {
        $domains = AllowedEmailDomain::values();

        expect($domains)
            ->toBeArray()
            ->toContain('drewroberts.com');
    });

    it('allows emails with drewroberts.com domain', function () {
        expect(AllowedEmailDomain::isAllowed('user@drewroberts.com'))->toBeTrue();
        expect(AllowedEmailDomain::isAllowed('admin@drewroberts.com'))->toBeTrue();
        expect(AllowedEmailDomain::isAllowed('test.user@drewroberts.com'))->toBeTrue();
    });

    it('rejects emails with unauthorized domains', function () {
        expect(AllowedEmailDomain::isAllowed('user@example.com'))->toBeFalse();
        expect(AllowedEmailDomain::isAllowed('user@gmail.com'))->toBeFalse();
        expect(AllowedEmailDomain::isAllowed('user@yahoo.com'))->toBeFalse();
        expect(AllowedEmailDomain::isAllowed('user@drewroberts.org'))->toBeFalse();
    });

    it('is case insensitive for domain checking', function () {
        expect(AllowedEmailDomain::isAllowed('user@DrewRoberts.com'))->toBeTrue();
        expect(AllowedEmailDomain::isAllowed('user@DREWROBERTS.COM'))->toBeTrue();
        expect(AllowedEmailDomain::isAllowed('user@DrEwRoBeRtS.cOm'))->toBeTrue();
    });

    it('handles emails without @ symbol gracefully', function () {
        expect(AllowedEmailDomain::isAllowed('invalidemail'))->toBeFalse();
    });

    it('handles empty strings', function () {
        expect(AllowedEmailDomain::isAllowed(''))->toBeFalse();
    });
});
