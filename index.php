<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
    body {
    background-image: url("3.jpg");
    background-size: cover;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center
    }
        
       .login-container {
    background-image: url("3.jpg");
    background-size: cover;        
    background-position: center;  
    background-repeat: no-repeat;  

    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(223, 116, 116, 0.2);

    width: 100%;
    max-width: 400px;
}
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: bold;
        }
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #dad4d4;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .message {
            text-align: center;
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 5px;
        }
        
        .error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .success {
            background: #dfd;
            color: #383;
            border: 1px solid #afa;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1rem;
            color: #666;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login to Dashboard</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your password">
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
