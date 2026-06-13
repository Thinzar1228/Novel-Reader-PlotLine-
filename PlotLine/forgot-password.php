<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstap/bootstrap.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <title>Forgot Password | Novela</title>
    <style>
        body {
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; background: #1c1f24; font-family: 'Inter', sans-serif;
        }
        .reset-wrapper {
            width: 400px; background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px); border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px; padding: 40px; color: #fff; text-align: center;
        }
        .input-box { position: relative; width: 100%; height: 50px; border-bottom: 2px solid #fff; margin: 30px 0; }
        .input-box input { width: 100%; height: 100%; background: transparent; border: none; outline: none; color: #fff; padding: 0 35px 0 5px; }
        .input-box label { position: absolute; top: 50%; left: 5px; transform: translateY(-50%); pointer-events: none; transition: .5s; }
        .input-box input:focus~label, .input-box input:not(:placeholder-shown)~label { top: -5px; }
        .btn-reset { width: 100%; height: 45px; background: #fff; border: none; border-radius: 40px; cursor: pointer; font-weight: 600; }
        .back-link { display: block; margin-top: 20px; color: #fff; text-decoration: none; font-size: 0.9em; opacity: 0.7; }
        .back-link:hover { opacity: 1; }
    </style>
</head>
<body>
    <div class="reset-wrapper">
        <h2 class="fw-bold">Reset Password</h2>
        <p class="small opacity-75">Enter email to receive a temporary password.</p>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sent'): ?>
            <div class="alert alert-success py-2 small">New password sent to your email!</div>
        <?php endif; ?>

        <form action="_actions/reset-request.php" method="post">
            <div class="input-box">
                <input type="email" name="email" placeholder=" " required>
                <label>Email</label>
            </div>
            <button type="submit" class="btn-reset">Send Password</button>
            <a href="index.php" class="back-link">Back to Login</a>
        </form>
    </div>
</body>
</html>