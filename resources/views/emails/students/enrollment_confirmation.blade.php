<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .content {
            margin-bottom: 20px;
        }
        .credentials {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Enrollment Confirmation</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $student->fname }} {{ $student->lname }},</p>
            
            <p>Congratulations! You have been successfully enrolled in the Pramukh SANKALP Programme.</p>
            
            <p>Here are your login credentials:</p>
            
            <div class="credentials">
                <p><strong>Username:</strong> {{ $username }}</p>
                <p><strong>Password:</strong> Your Registered Mobile Number ({{ $mobileNo }})</p>
            </div>
            
            <!-- <p><a href="{{ config('app.url') }}" style="display: inline-block; padding: 10px 20px; background-color: #3498db; color: #fff; text-decoration: none; border-radius: 3px;">Login to Portal</a></p> -->
            
            <p>If you have any questions, please contact the administration.</p>
        </div>
        
        <div class="footer">
            <p>Thanks,<br>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
