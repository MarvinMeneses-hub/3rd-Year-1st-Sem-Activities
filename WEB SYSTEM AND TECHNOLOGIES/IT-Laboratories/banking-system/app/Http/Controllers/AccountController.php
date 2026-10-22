<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::with('user')
            ->latest()
            ->paginate(10);

        return view('accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('accounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'minimum_balance' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            Account::create([
                'user_id' => $user->id,
                'account_number' => $this->generateAccountNumber(),
                'balance' => 0,
                'minimum_balance' => $validated['minimum_balance'],
            ]);
        });

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    private function generateAccountNumber(): string
    {
        do {
            $accountNumber = 'BANK-' . strtoupper(bin2hex(random_bytes(6)));
        } while (
            Account::where('account_number', $accountNumber)->exists()
        );

        return $accountNumber;
    }
}
