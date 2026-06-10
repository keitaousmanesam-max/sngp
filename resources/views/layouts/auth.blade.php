<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SNGP – @yield('title', 'Authentification')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #1B4F8A 0%, #2E75B6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1B4F8A;
        }

        .auth-logo p {
            color: #5D6D7E;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #1B4F8A;
            border-color: #1B4F8A;
        }

        .btn-primary:hover {
            background-color: #2E75B6;
            border-color: #2E75B6;
        }

        .form-control:focus {
            border-color: #2E75B6;
            box-shadow: 0 0 0 0.2rem rgba(46,117,182,0.25);
        }

        .badge-guinee {
            background-color: #E74C3C;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">
            <h1><i class="fas fa-pills"></i> SNGP</h1>
            <p>Système National de Gestion Pharmaceutique</p>
            <span class="badge-guinee">République de Guinée</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')

        <div class="text-center mt-4">
            <small class="text-muted">
                &copy; {{ date('Y') }} SNGP – Ministère de la Santé – République de Guinée
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>