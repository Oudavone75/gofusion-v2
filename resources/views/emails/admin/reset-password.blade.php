<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Password Reset</title>
    <style>
        /* General reset */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            /* Prevent font scaling in mobile */
            -ms-text-size-adjust: 100%;
            /* Prevent font scaling in mobile */
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
                            style="padding: 40px 30px 20px 30px; background-color: rgba(205, 246, 244, 1);">
                            <h1 style="color: black; font-size: 24px; margin: 0;">
                                {{ $data['header_title'] ?? 'Réinitialisez votre mot de passe' }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="padding: 20px 30px 40px 30px;">
                            <p style="font-size: 16px; color: #333333;">
                                Bonjour,
                            </p>
                            <p style="font-size: 16px; color: #333333;">
                                Vous recevez cet email parce que nous avons reçu une demande de réinitialisation de mot
                                de passe pour votre compte. Cliquez sur le bouton ci-dessous pour réinitialiser votre
                                mot de passe.
                            </p>

                            <!-- Action button -->
                            <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" class="button">
                                            {{ $data['url_title'] ?? 'Réinitialiser le mot de passe' }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 16px; color: #333333; margin-top: 20px;">
                                Si vous n’avez pas demandé de réinitialisation de mot de passe, aucune autre action n’est requise.
                            </p>

                            <p style="font-size: 14px; color: #333333; margin-top: 20px;">
                                Si le bouton « Réinitialiser le mot de passe » ne fonctionne pas, veuillez copier et coller le lien suivant dans votre navigateur: {{ $url }}
                            </p>
                            <p style="font-size: 16px; color: #333333;">
                                Merci,<br>
                                Go Fusion
                            </p>

                            <!-- Note about expiration -->
                            <p class="note">
                                Ce lien expirera dans deux heures.
                            </p>
                            <p>Merci de ne pas répondre à ce mail. Pour toute question, vous pouvez nous contacter à
                                l’adresse suivante : contact@gofusion.fr</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
