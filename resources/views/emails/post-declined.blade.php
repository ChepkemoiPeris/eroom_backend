<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Post Has Been Declined</title>
</head>
<body>
    <h1>We're Sorry!</h1>
    <p>Your post titled <strong>{{ $post->title }}</strong> has been declined.</p>
    <p>Reason for decline: <strong>{{ $declineReason }}</strong></p>
    <p>If you have any questions, please contact support.</p>
    <p>Regards,</p>
    <p>eRoom </p>
</body>
</html>
