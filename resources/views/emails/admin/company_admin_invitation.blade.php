<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Company Admin Invitation</title>
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
            color: #ffffff;
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
                    <tr>
                        <td align="center"
                            style="padding: 40px 30px 20px 30px; background-color: rgba(205, 246, 244, 1)">
                            <h1 style="color: black; font-size: 24px; margin: 0;">
                                Invitation Administrateur Entreprise
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="padding: 20px 30px 40px 30px;">
                            <p style="font-size: 16px; color: #333333;">
                                Bonjour,
                            </p>

                            @if ($isNewUser)
                                <p style="font-size: 16px; color: #333333;">
                                    Vous avez été invité à rejoindre le back-office de Go Fusion en tant
                                    qu’administrateur.
                                </p>
                                <p style="font-size: 16px; color: #333333;">
                                    Vos identifiants de connexion sont les suivants :
                                </p>
                                <p style="font-size: 16px; color: #333333;">
                                    Email: <strong>{{ $company->email }}</strong>
                                </p>
                                <p style="font-size: 16px; color: #333333;">
                                    Mot de passe: <strong>{{ $password }}</strong>
                                </p>
                                <p style="font-size: 16px; color: #333333;">
                                    👉 Se connecter au back-office
                                    Grâce à cet accès, vous pourrez :
                                </p>
                                <p style="font-size: 16px; color: #333333;">
                                    créer et gérer vos campagnes de défis,
                                    suivre les statistiques et l’engagement des utilisateurs,
                                    administrer les comptes et droits d’accès,
                                    personnaliser les contenus selon vos besoins.
                                </p>
                            @else
                                <p style="font-size: 16px; color: #333333;">
                                    Vous avez été désigné comme administrateur de
                                    <strong>{{ $company->name }}</strong>.
                                </p>
                            @endif

                            <!-- Action button -->
                            <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('company_admin.login') }}" class="button"
                                            style="background: linear-gradient(90deg, rgba(184, 0, 121, 1), rgba(240, 76, 184, 1)); border-color: none;">
                                            Aller à la connexion
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- @if ($isNewUser)
                                <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                    Please use your email address and the password above to login to the system.
                                </p>
                            @else
                                <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                    You can now access the company admin dashboard using your existing mobile
                                    application credentials.
                                </p>
                            @endif --}}

                            <p style="font-size: 14px; color: #333333; margin-top: 20px;">
                                Si le bouton ci-dessus ne fonctionne pas, copiez-collez directement cette URL dans votre navigateur :<br>
                                <a href="{{ route('company_admin.login') }}" style="color: #1a73e8; text-decoration: underline;">
                                    {{ route('company_admin.login') }}
                                </a>
                            </p>
                            <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                Merci de ne pas répondre à cet e-mail.
                            </p>
                            {{-- <p style="font-size: 16px; color: #333333;">
                                Thanks,<br>
                                {{ $company->name }}
                            </p> --}}
                            {{-- <p>Merci de ne pas répondre à ce mail. Pour toute question, vous pouvez nous contacter à
                                l’adresse suivante : contact@gofusion.fr</p>
                                 --}}
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
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
