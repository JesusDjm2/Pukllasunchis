@extends('layouts.app')
@section('titulo', 'Login - Intranet Pukllasunchis')
@section('content')
    <div class="login-wrapper">
        {{-- Capa con la imagen de fondo y la animación constante de zoom-in y zoom-out --}}
        <div class="login-bg-zoom"></div>
        <div class="login-bg-overlay"></div>

        <main class="login-main d-flex justify-content-center align-items-center py-4">
            <div class="container position-relative" style="z-index: 3;">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-9 col-lg-7 col-xl-5">
                        @if (Session::has('success'))
                            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert" style="border-radius: 12px;">
                                <i class="fa fa-circle-check me-2"></i>{{ Session::get('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Aviso contextual si el usuario se dirige al trámite de Carta de Aplicación --}}
                        @if (str_contains(url()->previous(), 'plan-de-trabajo') || str_contains(session('url.intended', ''), 'carta-aceptacion') || request()->has('carta'))
                            <div class="alert alert-warning border-0 shadow-sm mb-3 d-flex align-items-center" role="alert" style="border-radius: 12px; background: rgba(255, 248, 230, 0.95); backdrop-filter: blur(8px); border-left: 4px solid #cd9244 !important;">
                                <i class="fa fa-info-circle text-warning fs-4 me-2"></i>
                                <div style="font-size: 0.88rem; color: #5a4a28;">
                                    <strong>Carta de Aplicación:</strong> Ingresa con tu <strong>correo institucional (@pukllavirtual.edu.pe)</strong> para acceder al formulario.
                                </div>
                            </div>
                        @endif

                        <div class="login-card mx-auto">
                            <div class="login-card__header text-center">
                                <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="Pukllasunchis"
                                    class="login-card__logo mb-3">
                                <h1 class="login-card__title">Bienvenido a la Intranet</h1>
                                <p class="login-card__subtitle">Ingresa tus credenciales institucionales para acceder</p>
                            </div>

                            <form method="POST" action="{{ route('login') }}" id="loginForm" class="login-card__form" novalidate>
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label-modern">{{ __('Correo electrónico') }}</label>
                                    <div class="input-group input-group-modern @error('email') is-invalid @enderror">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" placeholder="usuario@pukllavirtual.edu.pe" required
                                            autocomplete="email" autofocus>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label-modern">{{ __('Contraseña') }}</label>
                                    <div class="input-group input-group-modern @error('password') is-invalid @enderror">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                        <input id="password" type="password"
                                            class="form-control @error('password') is-invalid @enderror" name="password"
                                            placeholder="••••••••" required autocomplete="current-password">
                                        <button class="btn btn-toggle-password" type="button" id="togglePassword"
                                            aria-label="Mostrar contraseña" tabindex="-1">
                                            <i class="fa fa-sm fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                            role="switch" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            {{ __('Recordarme') }}
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-login w-100" id="loginSubmit">
                                    <span class="btn-login__label">{{ __('Ingresar a Intranet') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .login-wrapper {
            position: relative;
            width: 100vw;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Imagen de fondo con zoom-in y zoom-out suave y constante */
        .login-bg-zoom {
            position: absolute;
            top: -6%;
            left: -6%;
            width: 112%;
            height: 112%;
            background-image: url('{{ asset('img/fondos/Fondo-nuevo-2.jpg') }}');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            z-index: 1;
            will-change: transform;
            animation: loginZoomInOut 16s ease-in-out infinite alternate;
        }

        @keyframes loginZoomInOut {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.12);
            }
        }

        /* Overlay sutil para realzar el contraste y legibilidad */
        .login-bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(20, 30, 45, 0.40), rgba(15, 23, 42, 0.55));
            z-index: 2;
        }

        .login-main {
            position: relative;
            z-index: 3;
            width: 100%;
            min-height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 18px;
            border-top: 4px solid #cd9244;
            box-shadow: 0 1.5rem 3rem rgba(10, 18, 30, 0.35);
            padding: 2.25rem 2rem 2rem;
        }

        .login-card__logo {
            width: 115px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .login-card__title {
            font-size: 1.45rem;
            font-weight: 700;
            color: #364651;
            margin-bottom: 0.25rem;
        }

        .login-card__subtitle {
            font-size: 0.88rem;
            color: #727272;
            margin-bottom: 1.75rem;
        }

        .form-label-modern {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #52658c;
            margin-bottom: 0.4rem;
            display: inline-block;
        }

        .input-group-modern .input-group-text {
            background: #f7f7f7;
            border-right: none;
            color: #cd9244;
            border-color: #dfe2e6;
        }

        .input-group-modern .form-control {
            border-left: none;
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
            font-size: 0.95rem;
        }

        .input-group-modern .form-control,
        .input-group-modern .input-group-text,
        .btn-toggle-password {
            border-color: #dfe2e6;
        }

        .input-group-modern:focus-within .input-group-text,
        .input-group-modern:focus-within .form-control,
        .input-group-modern:focus-within .btn-toggle-password {
            border-color: #cd9244;
        }

        .input-group-modern .form-control:focus {
            box-shadow: none;
        }

        .input-group-modern.is-invalid .form-control,
        .input-group-modern.is-invalid .btn-toggle-password {
            border-color: #dc3545;
        }

        .btn-toggle-password {
            background: #f7f7f7;
            border-left: none;
            color: #727272;
        }

        .btn-toggle-password:hover,
        .btn-toggle-password:focus {
            background: #f0f0f0;
            color: #364651;
        }

        .form-check.form-switch .form-check-input:checked {
            background-color: #cd9244;
            border-color: #cd9244;
        }

        .btn-login {
            background: linear-gradient(135deg, #d59d52, #cd9244);
            border: none;
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.02em;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
            box-shadow: 0 0.5rem 1.25rem rgba(205, 146, 68, 0.35);
        }

        .btn-login:hover,
        .btn-login:focus {
            transform: translateY(-2px);
            box-shadow: 0 0.75rem 1.5rem rgba(205, 146, 68, 0.45);
            color: #fff;
        }

        .btn-login:disabled {
            opacity: 0.75;
            transform: none;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 1.75rem 1.25rem 1.5rem;
                border-radius: 14px;
            }

            .login-card__logo {
                width: 95px;
            }

            .login-card__title {
                font-size: 1.25rem;
            }
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';

            passwordInput.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginSubmit');
            btn.disabled = true;
            btn.querySelector('.btn-login__label').innerHTML =
                '<i class="fa fa-spinner fa-spin me-2"></i>Ingresando...';
        });

        if (typeof gsap !== 'undefined') {
            gsap.from('.login-card', {
                opacity: 0,
                y: 30,
                duration: 0.7,
                ease: 'power3.out'
            });
            gsap.from('.login-card__logo, .login-card__title, .login-card__subtitle, .login-card__form .mb-3, .login-card__form .mb-4', {
                opacity: 0,
                y: 16,
                duration: 0.5,
                stagger: 0.07,
                delay: 0.2,
                ease: 'power2.out'
            });

            @if ($errors->any())
                gsap.fromTo('.login-card', {
                    x: 0
                }, {
                    x: 8,
                    duration: 0.06,
                    repeat: 5,
                    yoyo: true,
                    delay: 0.6,
                    ease: 'power1.inOut'
                });
            @endif
        }
    </script>
@endsection
