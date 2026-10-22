
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account | Banking System</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px 16px;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f7fb;
            color: #172033;
        }
        .container { max-width: 680px; margin: 0 auto; }
        .header { background: #142b4a; color: white; padding: 22px; border-radius: 14px 14px 0 0; }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .header p { margin-bottom: 0; color: #cbd8e8; }
        .panel {
            background: white;
            padding: 26px;
            border: 1px solid #e4eaf2;
            border-radius: 0 0 14px 14px;
            box-shadow: 0 8px 24px rgba(20,43,74,.05);
        }
        label { display: block; font-weight: 600; margin: 17px 0 7px; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
        }
        input:focus { outline: 2px solid #93c5fd; border-color: #2563eb; }
        .hint { font-size: .85rem; color: #65728a; margin-top: 6px; }
        .error { color: #b42318; font-size: .9rem; margin-top: 6px; }
        .alert {
            background: #fee2e2;
            color: #991b1b;
            border-radius: 9px;
            padding: 13px;
            margin-bottom: 16px;
        }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 24px; }
        .button {
            border: 0;
            display: inline-block;
            padding: 12px 17px;
            border-radius: 9px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .secondary { background: #e6edf7; color: #142b4a; }
    </style>
</head>
<body>
<div class="container">
    <header class="header">
        <h1>Create Bank Account</h1>
        <p>Register a new account holder.</p>
    </header>

    <main class="panel">
        @if ($errors->any())
            <div class="alert">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <label for="name">Account Holder Name</label>
            <input id="name" name="name" type="text" maxlength="255"
                   value="{{ old('name') }}" autocomplete="name" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror

            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" maxlength="255"
                   value="{{ old('email') }}" autocomplete="email" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror

            <label for="password">Initial Password</label>
            <input id="password" name="password" type="password"
                   minlength="8" autocomplete="new-password" required>
            <div class="hint">Use at least 8 characters.</div>
            @error('password') <div class="error">{{ $message }}</div> @enderror

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation"
                   type="password" minlength="8" autocomplete="new-password" required>

            <label for="minimum_balance">Minimum Balance (₱)</label>
            <input id="minimum_balance" name="minimum_balance" type="number"
                   min="0" max="9999999999999.99" step="0.01"
                   value="{{ old('minimum_balance', '100.00') }}" required>
            @error('minimum_balance')
                <div class="error">{{ $message }}</div>
            @enderror

            <div class="hint">
                New accounts start with a zero balance. Account numbers are generated automatically.
            </div>

            <div class="actions">
                <button class="button" type="submit">Create Account</button>
                <a class="button secondary" href="{{ route('accounts.index') }}">Cancel</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
