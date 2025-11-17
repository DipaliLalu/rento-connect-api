<!-- app/Views/emails/contact_us.php -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Contact Mail</title>
</head>
<body>
  <h2>Contact Request</h2>
  <p><strong>Name:</strong> <?= esc($name) ?></p>
  <p><strong>Email:</strong> <?= esc($email) ?></p>
  <p><strong>Subject:</strong> <?= esc($subject) ?></p>
  <p><strong>Message:</strong><br><?= nl2br(esc($message)) ?></p>
</body>
</html>
