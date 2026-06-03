<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IT Helpdesk RS Intan Husada</title>
    
    <!-- Styles -->
    <style>
        /* Custom Login CSS - RS Intan Husada */
        .login-body {
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            font-size: 15px;
            line-height: 1.7;
            color: #c4c3ca;
            background-color: #1f2029;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-logo {
            position: absolute;
            top: 30px;
            right: 30px;
            display: block;
            z-index: 100;
        }
        .login-logo span {
            color: #ffeba7;
            font-weight: 700;
            font-size: 18px;
        }
        .login-section {
            position: relative;
            width: 100%;
            display: block;
        }
        .login-full-height {
            min-height: 100vh;
        }
        
        /* PERBAIKAN: Switch Container untuk center yang benar */
        .login-switch-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            width: 100%;
            position: relative;
        }
        
        .login-checkbox:checked + label,
        .login-checkbox:not(:checked) + label {
            position: relative;
            display: inline-block;
            text-align: center;
            width: 60px;
            height: 16px;
            border-radius: 8px;
            padding: 0;
            margin: 0 15px;
            cursor: pointer;
            background-color: #ffeba7;
            vertical-align: middle;
        }
        
        .login-checkbox:checked + label:before,
        .login-checkbox:not(:checked) + label:before {
            position: absolute;
            display: block;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: #ffeba7;
            background-color: #102770;
            font-family: 'unicons';
            content: '\eb4f';
            z-index: 20;
            top: -10px;
            left: -10px;
            line-height: 36px;
            text-align: center;
            font-size: 24px;
            transition: all 0.5s ease;
        }
        
        .login-checkbox:checked + label:before {
            transform: translateX(44px) rotate(-270deg);
        }
        
        .login-card-3d-wrap {
            position: relative;
            width: 440px;
            max-width: 100%;
            height: 520px;
            -webkit-transform-style: preserve-3d;
            transform-style: preserve-3d;
            perspective: 800px;
            margin-top: 40px;
        }
        .login-card-3d-wrapper {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            -webkit-transform-style: preserve-3d;
            transform-style: preserve-3d;
            transition: all 600ms ease-out;
        }
        .login-card-front, .login-card-back {
            width: 100%;
            height: 100%;
            background-color: #2a2b38;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwMCAxMDAwIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9IiMyYTJiMzgiPjwvcmVjdD48L3N2Zz4=');
            background-position: bottom center;
            background-repeat: no-repeat;
            background-size: 300%;
            position: absolute;
            border-radius: 12px;
            left: 0;
            top: 0;
            -webkit-transform-style: preserve-3d;
            transform-style: preserve-3d;
            -webkit-backface-visibility: hidden;
            -moz-backface-visibility: hidden;
            -ms-backface-visibility: hidden;
            backface-visibility: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }
        .login-card-back {
            transform: rotateY(180deg);
        }
        
        /* PERBAIKAN UTAMA: Selector yang benar untuk animasi rotasi */
        #reg-log:checked ~ .login-card-3d-wrap .login-card-3d-wrapper {
            transform: rotateY(180deg);
        }
        
        .login-center-wrap {
            position: absolute;
            width: 100%;
            padding: 0 35px;
            top: 50%;
            left: 0;
            transform: translate3d(0, -50%, 35px) perspective(100px);
            z-index: 20;
            display: block;
            box-sizing: border-box;
        }
        .login-center-content {
            width: 100%;
            text-align: center;
        }
        .login-form-group {
            position: relative;
            display: block;
            margin: 0;
            padding: 0;
            margin-bottom: 20px;
        }
        .login-form-style {
            padding: 13px 20px;
            padding-left: 55px;
            height: 48px;
            width: 100%;
            font-weight: 500;
            border-radius: 8px;
            font-size: 14px;
            line-height: 22px;
            letter-spacing: 0.5px;
            outline: none;
            color: #c4c3ca;
            background-color: #1f2029;
            border: 2px solid transparent;
            transition: all 200ms linear;
            box-shadow: 0 4px 8px 0 rgba(21,21,21,.2);
            box-sizing: border-box;
        }
        .login-form-style:focus,
        .login-form-style:active {
            border: 2px solid #ffeba7;
            outline: none;
            box-shadow: 0 4px 8px 0 rgba(21,21,21,.2);
        }
        .login-input-icon {
            position: absolute;
            top: 0;
            left: 18px;
            height: 48px;
            font-size: 20px;
            line-height: 48px;
            text-align: left;
            color: #ffeba7;
            transition: all 200ms linear;
        }
        .login-btn {
            border-radius: 8px;
            height: 50px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            transition: all 200ms linear;
            padding: 0 30px;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: none;
            background: linear-gradient(135deg, #ffeba7, #ffd700);
            color: #102770;
            box-shadow: 0 8px 24px 0 rgba(255,235,167,.3);
            width: 100%;
            cursor: pointer;
            margin-top: 10px;
        }
        .login-btn:hover {
            background: linear-gradient(135deg, #102770, #1c3a7a);
            color: #ffeba7;
            box-shadow: 0 8px 24px 0 rgba(16,39,112,.3);
            transform: translateY(-2px);
        }
        .login-link {
            color: #c4c3ca;
            transition: all 200ms linear;
            text-decoration: none;
            font-size: 13px;
        }
        .login-link:hover {
            color: #ffeba7;
            text-decoration: underline;
        }
        .login-title {
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 24px;
            color: #ffeba7;
            text-align: center;
            width: 100%;
        }
        .login-subtitle {
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 18px;
            color: #ffeba7;
            text-align: center;
        }
        .login-switch-text {
            padding: 0 20px;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .login-error {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            text-align: center;
        }
        .help-info {
            background: rgba(255, 235, 167, 0.1);
            border: 1px solid #ffeba7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .help-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            color: #c4c3ca;
        }
        .help-icon {
            font-size: 18px;
            color: #ffeba7;
            margin-right: 12px;
            width: 20px;
            margin-top: 2px;
        }
        .help-text {
            flex: 1;
            font-size: 13px;
            text-align: left;
        }
        .help-phone {
            font-weight: 700;
            color: #ffeba7;
            font-size: 14px;
        }
        .help-email {
            color: #ffeba7;
            text-decoration: none;
            font-size: 13px;
        }
        .help-email:hover {
            text-decoration: underline;
        }
        [type="checkbox"]:checked,
        [type="checkbox"]:not(:checked) {
            position: absolute;
            left: -9999px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin: 15px 0;
            color: #c4c3ca;
            font-size: 13px;
            justify-content: center;
            text-align: center;
            width: 100%;
        }
        .remember-me label {
            text-align: center;
        }
        .help-description {
            color: #c4c3ca;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            width: 100%;
        }
        .help-note {
            color: #c4c3ca;
            margin-top: 15px;
            font-size: 11px;
            text-align: center;
            width: 100%;
        }
        .forgot-password {
            text-align: center;
            width: 100%;
            margin-top: 15px;
        }
        
        /* Container untuk memastikan semua elemen di tengah */
        .text-center-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        .form-container {
            width: 100%;
            max-width: 100%;
        }

        /* CSS UNTUK MOBILE RESPONSIVE */
        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
            }
            
            .login-logo {
                position: relative;
                top: auto;
                right: auto;
                text-align: center;
                margin-bottom: 20px;
            }
            
            .login-logo span {
                font-size: 16px;
            }
            
            .login-card-3d-wrap {
                width: 100%;
                height: 480px;
                margin-top: 20px;
            }
            
            .login-center-wrap {
                padding: 0 25px;
            }
            
            .login-title {
                font-size: 20px;
                margin-bottom: 25px;
            }
            
            .login-form-style {
                padding: 12px 15px;
                padding-left: 50px;
                height: 46px;
                font-size: 14px;
            }
            
            .login-input-icon {
                left: 15px;
                font-size: 18px;
                height: 46px;
                line-height: 46px;
            }
            
            .login-btn {
                height: 48px;
                font-size: 13px;
            }
            
            .login-switch-container {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .login-switch-text {
                font-size: 13px;
                padding: 0 15px;
            }
            
            .login-checkbox:checked + label,
            .login-checkbox:not(:checked) + label {
                margin: 0 10px;
                order: 0;
            }
            
            .help-info {
                padding: 12px;
            }
            
            .help-item {
                margin-bottom: 10px;
            }
            
            .help-icon {
                font-size: 16px;
                margin-right: 10px;
            }
            
            .help-text {
                font-size: 12px;
            }
            
            .help-phone {
                font-size: 13px;
            }
            
            .help-email {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .login-card-3d-wrap {
                height: 450px;
            }
            
            .login-center-wrap {
                padding: 0 20px;
            }
            
            .login-title {
                font-size: 18px;
                margin-bottom: 20px;
            }
            
            .login-form-style {
                padding: 10px 15px;
                padding-left: 45px;
                height: 44px;
                font-size: 13px;
            }
            
            .login-input-icon {
                left: 12px;
                font-size: 16px;
                height: 44px;
                line-height: 44px;
            }
            
            .login-btn {
                height: 46px;
                font-size: 12px;
            }
            
            .login-switch-text {
                font-size: 12px;
                padding: 0 12px;
            }
            
            .login-checkbox:checked + label,
            .login-checkbox:not(:checked) + label {
                width: 50px;
                margin: 0 8px;
            }
            
            .login-checkbox:checked + label:before,
            .login-checkbox:not(:checked) + label:before {
                width: 32px;
                height: 32px;
                line-height: 32px;
                font-size: 20px;
                top: -8px;
                left: -8px;
            }
            
            .login-checkbox:checked + label:before {
                transform: translateX(38px) rotate(-270deg);
            }
            
            .help-info {
                padding: 10px;
                max-height: 200px;
                overflow-y: auto;
            }
            
            .help-description {
                font-size: 13px;
                margin-bottom: 15px;
            }
            
            .help-note {
                font-size: 10px;
                margin-top: 12px;
            }
        }

        @media (max-width: 360px) {
            .login-switch-container {
                flex-direction: column;
                gap: 5px;
            }
            
            .login-switch-text {
                padding: 5px 0;
            }
            
            .login-checkbox:checked + label,
            .login-checkbox:not(:checked) + label {
                margin: 5px 0;
            }
        }
    </style>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700,800,900" rel="stylesheet">
    <link href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" rel="stylesheet">
</head>

<body class="login-body">
    <div class="login-container">
        <!-- Logo -->
        <div class="login-logo">
            <span>RS INTAN HUSADA</span>
        </div>

        <div class="section login-full-height">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 text-center align-self-center py-5">
                        <div class="section pb-5 pt-5 pt-sm-2 text-center">
                            <!-- PERBAIKAN: Switch Labels dengan container flex -->
                            <div class="login-switch-container">
                                <span class="login-switch-text" id="login-text">Log In</span>
                                
                                <!-- Switch Checkbox -->
                                <input class="login-checkbox" type="checkbox" id="reg-log" name="reg-log"/>
                                <label for="reg-log"></label>
                                
                                <span class="login-switch-text" id="help-text">Bantuan IT</span>
                            </div>
                            
                            <!-- 3D Card Container -->
                            <div class="login-card-3d-wrap mx-auto">
                                <div class="login-card-3d-wrapper">
                                    
                                    <!-- LOGIN CARD (Front) -->
                                    <div class="login-card-front">
                                        <div class="login-center-wrap">
                                            <div class="text-center-container">
                                                <h4 class="login-title">LOGIN SYSTEM</h4>
                                                
                                                <!-- Session Status -->
                                                <div style="color: #ffeba7; margin-bottom: 15px; font-size: 14px; text-align: center; width: 100%;">
                                                    <!-- Status message akan muncul di sini -->
                                                </div>

                                                <div class="form-container">
                                                    <form method="POST" action="#">
                                                        <!-- Email -->
                                                        <div class="login-form-group">
                                                            <input type="email" name="email" class="login-form-style" 
                                                                   placeholder="Email Address" value="" 
                                                                   required autofocus autocomplete="email">
                                                            <i class="login-input-icon uil uil-at"></i>
                                                        </div>

                                                        <!-- Password -->
                                                        <div class="login-form-group">
                                                            <input type="password" name="password" class="login-form-style" 
                                                                   placeholder="Password" required autocomplete="current-password">
                                                            <i class="login-input-icon uil uil-lock-alt"></i>
                                                        </div>

                                                        <!-- Remember Me -->
                                                        <div class="remember-me">
                                                            <input id="remember_me" type="checkbox" name="remember">
                                                            <label for="remember_me">Remember me</label>
                                                        </div>

                                                        <!-- Submit Button -->
                                                        <button type="submit" class="login-btn">
                                                            <i class="uil uil-sign-in-alt" style="margin-right: 8px;"></i>
                                                            LOGIN
                                                        </button>

                                                        <!-- Forgot Password -->
                                                        <div class="forgot-password">
                                                            <a href="#" class="login-link">
                                                                Lupa Password?
                                                            </a>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- HELP CARD (Back) -->
                                    <div class="login-card-back">
                                        <div class="login-center-wrap">
                                            <div class="text-center-container">
                                                <h4 class="login-title">BANTUAN IT</h4>
                                                <p class="help-description">
                                                    Hubungi tim IT untuk bantuan teknis
                                                </p>

                                                <div class="help-info">
                                                    <!-- Phone 1 -->
                                                    <div class="help-item">
                                                        <i class="help-icon uil uil-phone"></i>
                                                        <div class="help-text">
                                                            <div>IT Support Hotline</div>
                                                            <div class="help-phone">0812-3436-7980</div>
                                                        </div>
                                                    </div>

                                                    <!-- Phone 2 -->
                                                    <div class="help-item">
                                                        <i class="help-icon uil uil-phone"></i>
                                                        <div class="help-text">
                                                            <div>Emergency IT</div>
                                                            <div class="help-phone">0813-4567-8901</div>
                                                        </div>
                                                    </div>

                                                    <!-- Email -->
                                                    <div class="help-item">
                                                        <i class="help-icon uil uil-envelope"></i>
                                                        <div class="help-text">
                                                            <div>Email Support</div>
                                                            <a href="mailto:it.support@rsintanhusada.com" class="help-email">
                                                                it.support@rsintanhusada.com
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <!-- Hours -->
                                                    <div class="help-item">
                                                        <i class="help-icon uil uil-clock"></i>
                                                        <div class="help-text">
                                                            <div>Jam Operasional</div>
                                                            <div>Senin - Jumat: 07:30 - 15:00</div>
                                                            <div>Sabtu: 07:30 - 14:30</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <p class="help-note">
                                                    Untuk masalah darurat di luar jam operasional, 
                                                    hubungi nomor emergency IT
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('reg-log');
            const loginText = document.getElementById('login-text');
            const helpText = document.getElementById('help-text');

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    loginText.style.color = '#c4c3ca';
                    loginText.style.opacity = '0.7';
                    helpText.style.color = '#ffeba7';
                    helpText.style.opacity = '1';
                } else {
                    loginText.style.color = '#ffeba7';
                    loginText.style.opacity = '1';
                    helpText.style.color = '#c4c3ca';
                    helpText.style.opacity = '0.7';
                }
            });

            // Set initial colors
            loginText.style.color = '#ffeba7';
            loginText.style.opacity = '1';
            helpText.style.color = '#c4c3ca';
            helpText.style.opacity = '0.7';
        });
    </script>
</body>
</html>