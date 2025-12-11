<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Report</title>
</head>

<body style="font-family: Arial, sans-serif; background-color:#f7f7f7; padding:20px;">

    <table width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; margin:0 auto; background:#ffffff; padding:25px; border-radius:6px;">
        <tr>
            <td>

                <p style="font-size:16px; color:#333;">Hello {{ $user->name }},</p>

                <p style="font-size:15px; color:#333; line-height:1.6;">
                    {{ $message }}
                </p>

                @if($reportUrl)
                <p style="margin-top:20px;">
                    <a href="{{ $reportUrl }}"
                        style="display:inline-block; background:#4CAF50; color:#ffffff; padding:12px 20px; text-decoration:none; border-radius:4px; font-size:16px;">
                        Download the Report
                    </a>
                </p>
                @else
                <p style="font-size:15px; color:#333;">
                    The report is attached to this email.
                </p>
                @endif

                <br><br>

                <p style="font-size:15px; color:#333;">
                    Thanks,<br>
                    {{ config('app.name') }}
                </p>

            </td>
        </tr>
    </table>

</body>

</html>