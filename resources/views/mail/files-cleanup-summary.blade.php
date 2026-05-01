<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Files Cleanup Summary</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 32px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h1 style="font-size: 20px; font-weight: 700; color: #111827; margin-top: 0; margin-bottom: 8px;">
            Files Cleanup Summary
        </h1>
        <p style="font-size: 14px; color: #6b7280; margin-bottom: 24px;">
            The following {{ count($deletedFiles) }} file(s) were automatically deleted from
            <strong>{{ config('app.name') }}</strong> during the scheduled cleanup run.
        </p>

        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 10px 12px; background-color: #f9fafb; color: #374151; border-bottom: 2px solid #e5e7eb;">
                        File Name
                    </th>
                    <th style="text-align: left; padding: 10px 12px; background-color: #f9fafb; color: #374151; border-bottom: 2px solid #e5e7eb;">
                        Expired At
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deletedFiles as $file)
                <tr>
                    <td style="padding: 10px 12px; color: #111827; border-bottom: 1px solid #e5e7eb;">
                        {{ $file['original_name'] }}
                    </td>
                    <td style="padding: 10px 12px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">
                        {{ $file['expires_at'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="font-size: 12px; color: #9ca3af; margin-top: 24px; margin-bottom: 0;">
            This is an automated notification. No action is required.
        </p>
    </div>
</body>
</html>