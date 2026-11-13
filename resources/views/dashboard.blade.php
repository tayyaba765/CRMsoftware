<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Cork Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f6f8fc;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Dashboard */

        .dashboard-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .navbar-card {
            background: #ffffff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            padding: 18px 24px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #eaf2ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            font-weight: 700;
        }

        .brand-name {
            font-size: 21px;
            font-weight: 700;
            color: #172554;
        }

        .welcome-card {
            background: #ffffff;
            border: 1px solid #e8edf5;
            border-radius: 20px;
            padding: 35px;
            margin-top: 25px;
            box-shadow: 0 5px 25px rgba(15, 23, 42, 0.05);
        }

        .welcome-card h1 {
            font-size: 32px;
            font-weight: 700;
            color: #172554;
            margin-bottom: 10px;
        }

        .welcome-text {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 25px;
        }

        .role-badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .action-card {
            background: #ffffff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
        }

        .action-card h5 {
            color: #172554;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .action-card p {
            color: #64748b;
            margin-bottom: 20px;
        }

        .btn-techhub {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-techhub:hover {
            background: #1d4ed8;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .logout-btn {
            border-radius: 9px;
            padding: 7px 15px;
        }

        /* Splash Screen */

        .splash-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: #f8fbff;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: splashFade 0.8s ease forwards;
        }

        .splash {
            text-align: center;
            padding: 30px;
            max-width: 600px;
            animation: fadeUp 0.9s ease;
        }

        .splash-logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 28px;
            border-radius: 26px;
            background: #ffffff;
            color: #2563eb;
            border: 1px solid #e3ebf7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: 800;
            box-shadow: 0 12px 35px rgba(37, 99, 235, 0.10);
        }

        .splash h1 {
            color: #172554;
            font-size: 50px;
            font-weight: 750;
            letter-spacing: -1px;
            margin-bottom: 10px;
        }

        .tagline {
            color: #2563eb;
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .description {
            color: #64748b;
            font-size: 16px;
            line-height: 1.7;
            max-width: 520px;
            margin: 0 auto 32px;
        }

        .loader {
            width: 38px;
            height: 38px;
            border: 3px solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
            margin: 0 auto 15px;
        }

        .loading-text {
            color: #94a3b8;
            font-size: 14px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes splashFade {
            0% {
                opacity: 1;
            }

            80% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                visibility: hidden;
            }
        }

        @media (max-width: 576px) {
            .dashboard-wrapper {
                padding: 20px 15px;
            }

            .welcome-card {
                padding: 25px 20px;
            }

            .splash h1 {
                font-size: 40px;
            }

            .splash-logo {
                width: 85px;
                height: 85px;
            }
        }
    </style>
</head>

<body>

    <!-- Splash Screen -->
    <div class="splash-overlay">

        <div class="splash">

            <div class="splash-logo">
                TH
            </div>

            <h1>TechHub</h1>

            <div class="tagline">
                Your Technology Store
            </div>

            <p class="description">
                Welcome to TechHub — your trusted destination for
                quality technology products, accessories, and
                innovative solutions.
            </p>

            <div class="loader"></div>

            <div class="loading-text">
                Preparing your dashboard...
            </div>

        </div>

    </div>


    <!-- Dashboard -->
    <div class="dashboard-wrapper">

        <!-- Header -->
        <div class="navbar-card d-flex justify-content-between align-items-center">

            <div class="brand">
                <div class="brand-logo">
                    TH
                </div>

                <div class="brand-name">
                    TechHub
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="btn btn-outline-danger btn-sm logout-btn">
                    Logout
                </button>
            </form>

        </div>


        <!-- Welcome -->
        <div class="welcome-card">

            <h1>
                Welcome, {{ auth()->user()->name }}
            </h1>

            <p class="welcome-text">
                Welcome back to your TechHub dashboard.
                Manage your technology products and services from here.
            </p>

            <span class="role-badge">
                Role: {{ auth()->user()->getRoleNames()->implode(', ') }}
            </span>

        </div>



        <div class="action-card">

            <h5>
                {{ auth()->user()->getRoleNames()->implode(', ') }} Panel
            </h5>

            <p>
                Permissions:
                {{ auth()->user()->getAllPermissions()->pluck('name')->implode(', ') }}
            </p>

            <a href="{{ route('newdash') }}" class="btn btn-techhub">
                Go to {{ auth()->user()->getRoleNames()->implode(', ') }}
                Dashboard
            </a>

        </div>

        <!-- Manager -->


    </div>


    <!-- Splash Timer -->
    <script>
        setTimeout(function() {

            document.querySelector('.splash-overlay').style.display = 'none';

        }, 3000);
    </script>

</body>

</html>
