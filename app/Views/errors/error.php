<!DOCTYPE html>
<html>
<head>
    <title>Error <?php echo $code; ?></title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; background: #f5f5f5; }
        h1 { color: #d32f2f; }
    </style>
</head>
<body>
    <h1>Error <?php echo $code; ?></h1>
    <p><?php echo $message; ?></p>
    <a href="/">Go Home</a>
</body>
</html>
