<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #333333;
        }
        .content {
            margin-bottom: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
            color: #555555;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            color: #333333;
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #777777;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="content">
            <p><b>Bonjour {{ $user->first_name }},</b></p>
            <p>Merci d’avoir rejoint la communauté Go Fusion ! 🚀</p>
            <p>Tu es maintenant à un pas de commencer ton aventure, relever des défis, et gagner des récompenses 🎁 tout en agissant pour un monde meilleur 🌍.</p>
            <p>Voici ton code de vérification à utiliser dans les 10 prochaines minutes :</p>
            <div class="code">
                {{ $otp }}
            </div>
            <p>Entre ce code directement dans l’application pour activer ton compte et commencer à jouer. 🎮✨
            ⚡ Si tu n’es pas à l’origine de cette inscription, ignore simplement ce mail.</p>
        </div>
        <div class="footer">
            <p>À toi de jouer ! 💪<br>L’équipe Go Fusion</p>
            <img src="{{config('app.url')}}/go-fusion.png" alt="" class="light-logo">
            <p>Merci de ne pas répondre à ce mail. Pour toute question, vous pouvez nous contacter à l’adresse suivante : contact@gofusion.fr</p>
        </div>
    </div>

</body>
</html>
