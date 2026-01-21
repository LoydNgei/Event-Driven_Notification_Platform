<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notification Platform' }}</title>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .nav {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.75rem;
        }

        .nav a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .nav a:hover, .nav a.active {
            color: white;
        }

        .card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-pending { background: var(--gray-100); color: var(--gray-600); }
        .badge-processing { background: #dbeafe; color: #1d4ed8; }
        .badge-sent { background: #d1fae5; color: #065f46; }
        .badge-failed { background: #fee2e2; color: #991b1b; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray-600);
            background: var(--gray-50);
        }

        td {
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.25rem;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-600);
        }

        .code-block {
            background: var(--gray-800);
            color: #e5e7eb;
            font-family: ui-monospace, monospace;
            font-size: 0.75rem;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
        }

        .channel-icon {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📬 Notification Platform</h1>
            <nav class="nav">
                <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="/dashboard/trigger" class="{{ request()->is('dashboard/trigger') ? 'active' : '' }}">Trigger Event</a>
                <a href="/dashboard/templates" class="{{ request()->is('dashboard/templates*') ? 'active' : '' }}">Templates</a>
                <a href="/dashboard/rules" class="{{ request()->is('dashboard/rules*') ? 'active' : '' }}">Rules</a>
            </nav>
        </div>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
