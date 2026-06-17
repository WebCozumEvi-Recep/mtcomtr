<!DOCTYPE html>
<html>
<head>
    <title>Yeni İletişim Formu Başvurusu</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #ff7a00; border-bottom: 2px solid #ff7a00; padding-bottom: 10px;">Yeni İletişim Başvurusu</h2>
        <p>Aşağıdaki bilgilerle yeni bir iletişim formu gönderildi:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">AD SOYAD:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $data['name'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">GSM:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $data['phone'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">EPOSTA:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $data['email'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Tarih:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ now()->format('d.m.Y H:i') }}</td>
            </tr>
        </table>
        
        <div style="margin-top: 30px; font-size: 12px; color: #888; text-align: center;">
            Bu e-posta TekSat Tanıtım Sayfası üzerinden otomatik olarak gönderilmiştir.
        </div>
    </div>
</body>
</html>
