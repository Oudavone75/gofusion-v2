<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Account Created</title>
    <style>
        /* General reset */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #f4f4f4;
        }

        table {
            border-collapse: collapse !important;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Responsive design */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .button {
                width: 100% !important;
            }
        }

        /* Button styles */
        .button {
            font-size: 16px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            display: inline-block;
            border-radius: 5px;
            background: linear-gradient(90deg, rgba(184, 0, 121, 1), rgba(240, 76, 184, 1));
            border-color: none;
        }

        .button:hover {
            background: linear-gradient(90deg, rgba(184, 0, 121, 1), rgba(240, 76, 184, 1));
            border-color: none;
        }

        /* Small text note style */
        .note {
            font-size: 12px;
            margin-top: 20px;
            text-align: center;
            font-weight: bold !important;
        }
    </style>
</head>

<body style="background-color: #f4f4f4; font-family: Arial, sans-serif;">

    <!-- Email body -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 10px 0;">
                <!-- Email container -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="email-container"
                    style="background-color: #ffffff; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);">

                    <!-- Header -->
                    <tr>
                        <td align="center"
                            style="padding: 40px 30px 20px 30px; background-color: rgba(205, 246, 244, 1);">
                            <h1 style="color: black; font-size: 24px; margin: 0;">
                                Bienvenue sur {{ config('app.name') }} !
                            </h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td align="left" style="padding: 20px 30px 40px 30px;">
                            <p style="font-size: 16px; color: #333333;">
                                Bonjour <strong>{{ $sub_admin->name }}</strong>,
                            </p>

                            <p style="font-size: 16px; color: #333333;">
                                Votre compte administrateur a été créé avec succès. Vous avez obtenu l’accès au panneau
                                d’administration de {{ config('app.name') }} en tant que
                                <strong>{{ $sub_admin->roles->first()->name ?? 'Administrator' }}</strong>.
                            </p>

                            <p style="font-size: 16px; color: #333333;">
                                Vos identifiants de connexion sont les suivants :
                            </p>

                            <!-- Credentials Box -->
                            <div class="credentials-box">
                                <p>
                                    <strong>E-mail :</strong> {{ $sub_admin->email }}
                                </p>
                                <p>
                                    <strong>Mot de passe :</strong> {{ $password }}
                                </p>
                                <p>
                                    <strong>Rôle :</strong> {{ $sub_admin->roles->first()->name ?? 'N/A' }}
                                </p>
                            </div>

                            <p style="font-size: 16px; color: #333333;">
                                👉 Avec cet accès, vous pourrez :
                            </p>

                            @if ($sub_admin->roles->first()->name == 'Manager')
                                <ul style="font-size: 16px; color: #333333; line-height: 1.8;">
                                    <li>Peut créer et suivre des campagnes dans son propre périmètre (ex. : département,
                                        division ou site).</li>
                                    <li>Peut valider les défis et consulter les résultats de son équipe.</li>
                                    <li>Ne peut pas supprimer ni modifier les campagnes globales.</li>
                                    <li>Profil type : manager local / ambassadeur interne.</li>
                                </ul>
                            @else
                                <ul style="font-size: 16px; color: #333333; line-height: 1.8;">
                                    <li>Accès en lecture seule aux tableaux de bord (résultats, taux de participation,
                                        classements, etc.).</li>
                                    <li>Ne peut rien modifier ni créer.</li>
                                    <li>Profil type : direction / RH / direction générale souhaitant suivre la
                                        progression.</li>
                                </ul>
                            @endif

                            <!-- Action button -->
                            <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0"
                                role="presentation" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ Auth::guard('admin')->check() ? route('admin.login') : route('company_admin.login') }}"
                                            class="button"
                                            style="background: linear-gradient(90deg, rgba(184, 0, 121, 1), rgba(240, 76, 184, 1)); border: none;">
                                            Aller à la connexion
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #333333; margin-top: 20px;">
                                Si le bouton ci-dessus ne fonctionne pas, copiez-collez directement cette URL dans votre
                                navigateur :<br>
                                <a href="{{ Auth::guard('admin')->check() ? route('admin.login') : route('company_admin.login') }}"
                                    style="color: #1a73e8; text-decoration: underline;">
                                    {{ Auth::guard('admin')->check() ? route('admin.login') : route('company_admin.login') }}
                                </a>
                            </p>
                            <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                Merci de ne pas répondre à cet e-mail.
                            </p>

                            <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                Pour toute question, vous pouvez nous écrire à : contact@gofusion.fr
                            </p>
                            <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                À très bientôt,
                            </p>
                            <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                L’équipe Go Fusion 🌱
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding: 20px 30px; background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
                            <p style="font-size: 12px; color: #6c757d; margin: 0;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                            <p style="font-size: 12px; color: #6c757d; margin: 5px 0 0 0;">
                                This is an automated message, please do not reply.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
