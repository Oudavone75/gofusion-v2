<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <style>
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
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        table {
            border-collapse: collapse !important;
        }

        .email-container {
            background-color: #ffffff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

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
        }

        .button:hover {
            opacity: 0.9;
        }

        h1 {
            color: black;
            font-size: 26px;
            margin: 0;
        }

        p {
            font-size: 16px;
            color: #333333;
            line-height: 1.6;
        }

        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .button {
                width: 100% !important;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
        <tr>
            <td align="center" valign="middle" style="padding: 20px;">
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="email-container">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 30px 20px 30px; background-color: rgba(205, 246, 244, 1);">
                            <h1>Accès non autorisé 🚫</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td align="left" style="padding: 20px 30px 40px 30px;">
                            <p>
                                Désolé, vous n’avez pas la permission d’accéder à cette page sur
                                <strong>{{ config('app.name') }}</strong>.
                            </p>

                            <p>
                                Si vous pensez qu’il s’agit d’une erreur, veuillez contacter l’administrateur du système
                                pour obtenir de l’aide.
                            </p>

                            <div style="text-align:center; margin-top:30px;">
                                <a href="{{ Auth::guard('admin')->check() ? route('admin.dashboard') : route('company_admin.dashboard') }}"
                                    class="button">
                                    Retour à l'accueil
                                </a>
                            </div>

                            <p style="font-size:14px; color:#555; text-align:center; margin-top:30px;">
                                Code d’erreur : 403 | Accès refusé
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding: 20px 30px; background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
                            <p style="font-size: 12px; color: #6c757d; margin: 0;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
                            </p>
                            <p style="font-size: 12px; color: #6c757d; margin: 5px 0 0 0;">
                                Ceci est une page système, veuillez ne pas répondre.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
