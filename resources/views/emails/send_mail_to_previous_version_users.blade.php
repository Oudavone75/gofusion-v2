{{-- @dd($code) --}}
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go Fusion - Nouvelle Version</title>
</head>

<body style="margin:0; padding:0; background-color:#ffffff; font-family: Arial, Helvetica, sans-serif; color:#000000;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#ffffff; padding:20px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="background-color:#ffffff; border-collapse:collapse;">
                    <tr>
                        <td style="padding:40px 30px; font-size:15px; line-height:1.6; color:#000000;">

                            <p style="margin:0 0 20px;">Bonjour,</p>

                            <p style="margin:0 0 25px;">
                                Nous avons le plaisir de vous annoncer le <strong>lancement de la toute nouvelle version
                                    de Go Fusion 🎉</strong> — plus fluide, plus intuitive et encore plus fun !
                            </p>

                            <p style="margin:0 0 15px;">
                                👉 Avant de commencer, nous vous recommandons de <strong>désinstaller l’ancienne
                                    version</strong> de l’application, puis de <strong>télécharger la nouvelle</strong>,
                                disponible sur
                                <a href="https://apps.apple.com/fr/app/go-fusion-app/id6503088235"
                                    style="color:#1a0dab; text-decoration:underline;">l’App Store</a> ou
                                <a href="https://play.google.com/store/apps/details?id=com.gofusionapp.gofusionapp&hl=en"
                                    style="color:#1a0dab; text-decoration:underline;">Google Play</a>.
                            </p>

                            <p style="margin:20px 0 25px;">
                                Pour vous connecter, un <strong>mot de passe provisoire</strong> vous a été attribué ;
                                vous pourrez bien sûr le <strong>modifier à tout moment</strong> depuis votre profil.
                            </p>

                            <p style="margin:0 0 25px;"><strong>Mot de passe provisoire :</strong> {{ $password }}
                            </p>

                            @if ($code != null)

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                    style="border-top:1px solid #ddd; margin:25px 0;">
                                    <tr>
                                        <td style="padding-top:20px;">
                                            <p style="margin:0 0 10px; font-weight:bold;">🔐 Votre code d’accès</p>
                                            <p style="margin:0 0 10px;">
                                                Ce code vous permet d’intégrer directement votre espace dédié à votre
                                                entreprise Inéo.
                                            </p>
                                            <p style="margin:0;"><strong>Code :</strong> {{ $code }}</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border-top:1px solid #ddd; margin:25px 0;">
                                <tr>
                                    <td style="padding-top:20px;">
                                        <p style="margin:0 0 10px; font-weight:bold;">🌱 Quoi de neuf ?</p>
                                        <p style="margin:0;">
                                            Go Fusion évolue pour vous offrir une <strong>expérience par “campagne” de 3
                                                semaines</strong>, rythmées de défis courts, de quiz, et d’actions
                                            concrètes pour renforcer l’engagement collectif.
                                            Chaque session met en avant un <strong>thème stratégique différent</strong>
                                            (santé & bien-être, climat, inclusion, innovation, etc.), et vous pouvez
                                            suivre vos points et classements en temps réel.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border-top:1px solid #ddd; margin:25px 0;">
                                <tr>
                                    <td style="padding-top:20px;">
                                        <p style="margin:0 0 10px; font-weight:bold;">💚 Pas encore de campagne ?</p>
                                        <p style="margin:0;">
                                            Pas d’inquiétude ! Vous pouvez basculer en <strong>mode Citoyen</strong>, un
                                            espace ouvert à tous pour continuer à relever des défis écologiques,
                                            solidaires et inspirants.
                                            Dès qu’une nouvelle campagne de votre entreprise sera disponible,
                                            <strong>vous en serez informé directement dans l’application</strong>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 15px;">
                                Merci pour votre <strong>fidélité et votre enthousiasme</strong> depuis nos débuts 💚
                            </p>

                            <p style="margin:0 0 20px;">
                                C’est grâce à vous que Go Fusion continue d’évoluer pour rendre chaque action plus
                                ludique et plus impactante.
                            </p>

                            <p style="margin:0 0 15px;">À très vite dans la nouvelle aventure Go Fusion 🚀</p>

                            <p style="margin:0; font-weight:600;">
                                Bien à vous,<br>
                                L’équipe Go Fusion
                            </p>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
