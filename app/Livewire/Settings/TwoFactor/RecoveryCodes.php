<?php

namespace App\Livewire\Settings\TwoFactor;

use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RecoveryCodes extends Component
{
    /** @var array<int, string> */
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $generateNewRecoveryCodes($user);

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $decrypted = decrypt($user->two_factor_recovery_codes);
                $decoded = json_decode(is_string($decrypted) ? $decrypted : '', true);
                $this->recoveryCodes = is_array($decoded) ? $decoded : [];
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }
}
