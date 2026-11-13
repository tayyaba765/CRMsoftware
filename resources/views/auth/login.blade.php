<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />


    <!-- Include Vite here too -->
    <script src="https://cdn.tailwindcss.com"></script>
    <title>{{ $mode === 'register' ? 'Create account' : 'Sign in' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#5b3fd4',
                            dark: '#4629b8'
                        },
                        page: '#f1f2f3',
                        ink: '#0e1726',
                        muted: '#6b7280'
                    }
                }
            }
        };
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .field {
                @apply w-full rounded-lg border border-gray-200 bg-[#f8f9fa] px-3.5 py-2 text-sm outline-none transition placeholder:text-gray-400 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/20;
            }

            .field.invalid {
                @apply border-red-400 focus:border-red-400 focus:ring-red-200;
            }

            .lbl {
                @apply mb-1 block text-xs font-semibold;
            }

            .toggle {
                @apply absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-brand;
            }

            .err {
                @apply mt-0.5 min-h-[14px] text-[11px] leading-[14px] text-red-500;
            }

            .btn {
                @apply w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2;
            }

            .ghost {
                @apply rounded-lg border border-white/70 px-7 py-2.5 text-sm font-semibold transition hover:bg-white hover:text-brand focus:outline-none focus-visible:ring-2 focus-visible:ring-white;
            }
        }
    </style>
    <style>
        /* No animation on first paint, so either page opens instantly in the right state */
        .panel {
            transition: none;
        }

        body.ready .panel {
            transition: transform .6s cubic-bezier(.65, 0, .35, 1), opacity .5s ease;
        }

        .copy {
            transition: opacity .4s ease;
        }

        #auth[data-mode="login"] #registerPanel,
        #auth[data-mode="register"] #loginPanel {
            opacity: 0;
            pointer-events: none;
        }

        #registerPanel {
            z-index: 1;
        }

        #auth[data-mode="register"] #registerPanel {
            z-index: 2;
        }

        #auth[data-mode="login"] .copy-register,
        #auth[data-mode="register"] .copy-login {
            opacity: 0;
            pointer-events: none;
        }

        @media (min-width: 768px) {

            #auth[data-mode="register"] #loginPanel,
            #auth[data-mode="register"] #registerPanel {
                transform: translateX(100%);
            }

            #auth[data-mode="register"] #slider {
                transform: translateX(-100%);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            body.ready .panel,
            .copy {
                transition: none;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-page font-sans text-ink flex items-center justify-center p-4">

    <main id="auth" data-mode="{{ $mode }}"
        class="relative w-full max-w-[960px] h-[680px] md:h-[520px] overflow-hidden rounded-2xl bg-white shadow-[0_6px_20px_rgba(14,23,38,.08)]">

        <!-- LOGIN -->
        <section id="loginPanel" class="panel absolute top-0 left-0 h-full w-full md:w-1/2 overflow-y-auto">
            <form action="{{ route('login') }}" method="POST" id="loginForm" novalidate
                class="flex min-h-full flex-col justify-center px-8 md:px-10 py-6">
                @csrf
                <!-- property errors ->login -->
                @if ($errors->login->any())
                    <div class="bg-red-50 border border-red-200 p-4 rounded-lg space-y-2 mb-4 shadow-sm">
                        <div class="font-semibold text-red-800 text-sm">Please fix the following errors:</div>

                        @foreach ($errors->login->all() as $error)
                            <div class="bg-red-100 text-red-700 px-3 py-2 rounded text-sm font-medium" role="alert">
                                {{ $error }}
                            </div>
                        @endforeach
                    </div>
                @endif


                <div class="mb-4 flex items-center gap-2 md:hidden">
                    <svg width="26" height="26" viewBox="0 0 32 32" aria-hidden="true">
                        <circle cx="16" cy="16" r="14" fill="#5b3fd4" />
                        <circle cx="16" cy="16" r="5.5" fill="#fff" />
                    </svg>
                    <span class="text-lg font-bold tracking-wide">CORK</span>
                </div>
                <h1 class="text-2xl font-semibold">Sign in</h1>
                <p class="mt-1 mb-5 text-sm text-muted">Enter your email and password to continue.</p>

                <label class="lbl" for="email">Email</label>
                <input id="loginEmail" type="email" name="email" autocomplete="email" placeholder="you@example.com"
                    class="field" value="{{ old('email') }}" autofocus />
                <p class="err" data-for="loginEmail"></p>

                <label class="lbl mt-2" for="loginPassword">Password</label>
                <div class="relative">
                    <input id="loginPassword" name="password" type="password" autocomplete="current-password"
                        placeholder="Your password" class="field pr-14" />
                    <button type="button" data-toggle="loginPassword" class="toggle">Show</button>
                </div>
                <p class="err" data-for="loginPassword"></p>

                <div class="mt-2 flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-muted cursor-pointer">
                        <input name="remember" value="1" type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 accent-[#5b3fd4]" /> Remember me
                    </label>
                    <a href="#" class="font-medium text-brand hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="btn mt-5">Sign in</button>
                <p class="msg mt-2 min-h-[20px] text-center text-sm" aria-live="polite"></p>

                <p class="text-center text-sm text-muted md:hidden">
                    New here? <button type="button" data-switch="register" class="font-semibold text-brand">Create an
                        account</button>
                </p>
            </form>
        </section>

        <!-- REGISTER -->
        <section id="registerPanel" class="panel absolute top-0 left-0 h-full w-full md:w-1/2 overflow-y-auto">
            <form action="{{ route('register') }}" method="POST" id="registerForm" novalidate
                class="flex min-h-full flex-col justify-center px-8 md:px-10 py-6">
                @csrf
                @if ($errors->register->any())
                    <div class="bg-red-50 border border-red-200 p-4 rounded-lg space-y-2 mb-4 shadow-sm">
                        <div class="font-semibold text-red-800 text-sm">Please fix the following errors:</div>

                        @foreach ($errors->register->all() as $error)
                            <div class="bg-red-100 text-red-700 px-3 py-2 rounded text-sm font-medium" role="alert">
                                {{ $error }}
                            </div>
                        @endforeach
                    </div>
                @endif

                <h1 class="text-2xl font-semibold">Create account</h1>
                <p class="mt-1 mb-4 text-sm text-muted">It takes less than a minute.</p>

                <label class="lbl" for="regName">Full name</label>
                <input id="regName" name="name" type="text" autocomplete="name" placeholder="Your name"
                    class="field" />
                <p class="err" data-for="regName"></p>

                <label class="lbl mt-1" for="regEmail">Email</label>
                <input id="regEmail" name="email" type="email" autocomplete="email"
                    placeholder="you@example.com" class="field" />
                <p class="err" data-for="regEmail"></p>

                <div class="mt-1 grid gap-x-3 sm:grid-cols-2">
                    <div>
                        <label class="lbl" for="regPassword">Password</label>
                        <div class="relative">
                            <input id="regPassword" name="password" type="password" autocomplete="new-password"
                                placeholder="8+ characters" class="field pr-14" />
                            <button type="button" data-toggle="regPassword" class="toggle">Show</button>
                        </div>
                        <p class="err" data-for="regPassword"></p>
                    </div>
                    <div>
                        <label class="lbl" for="regConfirm">Confirm</label>
                        <div class="relative">
                            <input id="regConfirm" name="password_confirmation" type="password"
                                autocomplete="new-password" placeholder="Repeat password" class="field" />
                            <button type="button" data-toggle="regConfirm" class="toggle">Show</button>
                        </div>
                        <p class="err" data-for="regConfirm"></p>
                    </div>

                    <label class="mt-1 flex items-start gap-2 text-xs text-muted cursor-pointer">
                        <input id="regTerms" type="checkbox" name="terms" value="1"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 accent-[#5b3fd4]" />
                        <span>I agree to the <a href="#" class="font-medium text-brand hover:underline">Terms
                                &amp;
                                Conditions</a></span>
                    </label>
                    <p class="err" data-for="regTerms"></p>

                    <button type="submit" class="btn mt-3">Create account</button>
                    <p class="msg mt-2 min-h-[20px] text-center text-sm" aria-live="polite"></p>

                    <p class="text-center text-sm text-muted md:hidden">
                        Already registered? <button type="button" data-switch="login"
                            class="font-semibold text-brand">Sign in</button>
                    </p>
            </form>
        </section>


        <aside id="slider"
            class="panel hidden md:block absolute top-0 left-1/2 z-10 h-full w-1/2 bg-gradient-to-br from-[#6d4bd8] to-[#3f26a8] text-white">
            <div class="absolute left-8 top-7 flex items-center gap-2">
                <svg width="26" height="26" viewBox="0 0 32 32" aria-hidden="true">
                    <circle cx="16" cy="16" r="14" fill="#fff" />
                    <circle cx="16" cy="16" r="5.5" fill="#5b3fd4" />
                </svg>
                <span class="text-lg font-bold tracking-wide">CORK</span>
            </div>
            <div class="copy copy-login absolute inset-0 flex flex-col items-center justify-center px-12 text-center">
                <h2 class="text-2xl font-semibold">New here?</h2>
                <p class="mt-3 mb-6 text-sm leading-relaxed text-white/80">Create an account to get started with your
                    dashboard.</p>
                <button type="button" data-switch="register" class="ghost">Create account</button>
            </div>
            <div
                class="copy copy-register absolute inset-0 flex flex-col items-center justify-center px-12 text-center">
                <h2 class="text-2xl font-semibold">Welcome back</h2>
                <p class="mt-3 mb-6 text-sm leading-relaxed text-white/80">Already have an account? Sign in to pick up
                    where you left off.</p>
                <button type="button" data-switch="login" class="ghost">Sign in</button>
            </div>
        </aside>
    </main>

    <script>
        const $ = id => document.getElementById(id);
        const auth = $('auth'),
            loginPanel = $('loginPanel'),
            registerPanel = $('registerPanel');
        const pages = {
            login: '{{ route('login') }}',
            register: '{{ route('register') }}'
        };
        const titles = {
            login: 'Sign in',
            register: 'Create account'
        };

        function setMode(mode, push) {
            auth.dataset.mode = mode;
            loginPanel.inert = mode !== 'login'; // hidden form can't be tabbed into
            registerPanel.inert = mode !== 'register';
            document.title = titles[mode];
            if (push) {
                try {
                    history.pushState({}, '', pages[mode]);
                } catch (e) {}
            } // works when served over http
        }
        setMode(auth.dataset.mode, false);
        requestAnimationFrame(() => requestAnimationFrame(() => document.body.classList.add('ready')));

        document.querySelectorAll('[data-switch]').forEach(b =>
            b.addEventListener('click', () => setMode(b.dataset.switch, true))
        );
        addEventListener('popstate', () => setMode(/register/.test(location.pathname) ? 'register' : 'login', false));


        document.querySelectorAll('[data-toggle]').forEach(btn =>
            btn.addEventListener('click', () => {
                const input = $(btn.dataset.toggle),
                    show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.textContent = show ? 'Hide' : 'Show';
            })
        );

        const emailOk = v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);

        function setError(id, msg) {
            document.querySelector(`.err[data-for="${id}"]`).textContent = msg || '';
            if ($(id).type !== 'checkbox') $(id).classList.toggle('invalid', !!msg);
            return !msg;
        }

        function showMsg(form, text) {
            const m = form.querySelector('.msg');
            m.textContent = text;
            m.className = 'msg mt-2 min-h-[20px] text-center text-sm text-green-600';
        }
        document.querySelectorAll('input').forEach(i =>
            i.addEventListener('input', () => {
                if (i.id) setError(i.id, '');
            })
        );
        $('regTerms').addEventListener('change', () => setError('regTerms', ''));

        $('loginForm').addEventListener('submit', e => {
            const email = $('loginEmail').value.trim(),
                pass = $('loginPassword').value;
            const ok = [
                setError('loginEmail', emailOk(email) ? '' : 'Enter a valid email address.'),
                setError('loginPassword', pass ? '' : 'Enter your password.'),
            ];
            if (ok.includes(false)) {
                e.preventDefault();
                return;
            }

        });

        $('registerForm').addEventListener('submit', e => {
            const name = $('regName').value.trim(),
                email = $('regEmail').value.trim();
            const pass = $('regPassword').value,
                confirm = $('regConfirm').value;
            const ok = [
                setError('regName', name.length >= 2 ? '' : 'Enter your full name.'),
                setError('regEmail', emailOk(email) ? '' : 'Enter a valid email address.'),
                setError('regPassword', pass.length >= 8 ? '' : 'Use at least 8 characters.'),
                setError('regConfirm', confirm && confirm === pass ? '' : 'Passwords do not match.'),
                setError('regTerms', $('regTerms').checked ? '' : 'Accept the terms to continue.'),
            ];
            if (ok.includes(false)) {
                e.preventDefault();
                return;
            }

        });
    </script>
</body>

</html>
