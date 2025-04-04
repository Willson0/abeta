<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Success</title>

    <style>
        .notify_calendly {
            position:fixed;
            width:100%;
            height:100%;
            top:0;
            left:0;
            background-color: #f3f4f6;
        }

        .notify_calendly>div {
            display:flex;
            flex-direction:column;
            row-gap:16px;
            position:absolute;
            top:50%;
            left:50%;
            transform: translate(-50%, -50%);
        }

        .notify_calendly>div>img {
            width:300px;
        }

        .notify_calendly_text {
            text-align:center;
            font-family: 'inter', serif;
            font-weight:500;
            font-size:16px;
            line-height:24px;
        }
    </style>
</head>
<body>
<div class="notify_calendly">
    <div>
        <img src="/storage/successBubble.png" alt="">
        <div class="notify_calendly_text">Можете вернуться<br>в Telegram</div>
    </div>
</div>
</body>
</html>
