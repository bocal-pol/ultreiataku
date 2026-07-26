<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <title>
        @if ($locale === 'nl') Uitnodiging Ultreiataku
        @elseif ($locale === 'de') Einladung Ultreiataku
        @else Invitation Ultreiataku
        @endif
    </title>
</head>
<body style="font-family: sans-serif; max-width: 600px; margin: auto; padding: 24px; color: #1D110D;">

    @if ($locale === 'nl')
        <h1 style="color: #D96B43;">Uitnodiging voor een pèlerinage</h1>
        <p>{{ $organizerName }} nodigt u uit om deel te nemen aan de pelgrimsreis:</p>
        <p style="font-size: 1.2em; font-weight: bold;">{{ $tripName }}</p>
        <p>Klik op onderstaande knop om de uitnodiging te aanvaarden:</p>
        <a href="{{ $joinUrl }}" style="background:#D96B43;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;margin:16px 0;">
            Deelnemen aan de reis
        </a>
        <p style="color:#666;font-size:0.9em;">
            Als de knop niet werkt, kopieer dan deze link:<br>
            <a href="{{ $joinUrl }}">{{ $joinUrl }}</a>
        </p>
        <p style="color:#666;font-size:0.85em;">
            Deze uitnodiging blijft geldig totdat de organisator ze intrekt.<br>
            U heeft een Ultreiataku-account nodig om deel te nemen.
        </p>
    @elseif ($locale === 'de')
        <h1 style="color: #D96B43;">Einladung zur Pilgerreise</h1>
        <p>{{ $organizerName }} lädt Sie ein, an der Pilgerreise teilzunehmen:</p>
        <p style="font-size: 1.2em; font-weight: bold;">{{ $tripName }}</p>
        <p>Klicken Sie auf die Schaltfläche unten, um die Einladung anzunehmen:</p>
        <a href="{{ $joinUrl }}" style="background:#D96B43;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;margin:16px 0;">
            An der Reise teilnehmen
        </a>
        <p style="color:#666;font-size:0.9em;">
            Falls die Schaltfläche nicht funktioniert, kopieren Sie diesen Link:<br>
            <a href="{{ $joinUrl }}">{{ $joinUrl }}</a>
        </p>
        <p style="color:#666;font-size:0.85em;">
            Diese Einladung ist gültig, bis der Organisator sie widerruft.<br>
            Sie benötigen ein Ultreiataku-Konto, um teilzunehmen.
        </p>
    @else
        <h1 style="color: #D96B43;">Invitation à un pèlerinage</h1>
        <p>{{ $organizerName }} vous invite à rejoindre le voyage de pèlerinage :</p>
        <p style="font-size: 1.2em; font-weight: bold;">{{ $tripName }}</p>
        <p>Cliquez sur le bouton ci-dessous pour accepter l'invitation :</p>
        <a href="{{ $joinUrl }}" style="background:#D96B43;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;margin:16px 0;">
            Rejoindre le voyage
        </a>
        <p style="color:#666;font-size:0.9em;">
            Si le bouton ne fonctionne pas, copiez ce lien :<br>
            <a href="{{ $joinUrl }}">{{ $joinUrl }}</a>
        </p>
        <p style="color:#666;font-size:0.85em;">
            Cette invitation reste valide jusqu'à ce que l'organisateur la révoque.<br>
            Vous aurez besoin d'un compte Ultreiataku pour participer.
        </p>
    @endif

    <hr style="border:none;border-top:1px solid #F3EBE3;margin:24px 0;">
    <p style="color:#999;font-size:0.8em;">
        Ultreiataku — {{ config('app.url') }}
    </p>
</body>
</html>
