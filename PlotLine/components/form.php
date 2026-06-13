<div id="custom-alert"></div>

    <div class="wrapper">
        <span class="icon-close"><ion-icon name="close"></ion-icon></span>
        <div class="form-box login">
            <h2>Login</h2>
            <form action="_actions/login.php" method="post" autocomplete="off">
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="email" autocomplete="off" name="email" placeholder=" " required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <span class="icon lock-icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <span class="icon eye-toggle" style="display: none; cursor: pointer;">
                    <ion-icon name="eye"></ion-icon>
                    </span>
                    <input type="password" name="password" autocomplete="new-password" placeholder=" " required>
                    <label>Password</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox" name="remember_me"> Remember me</label>
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn">Login</button>
                <div class="login-register">
                    <p>Don't have an account? <a href="#" class="register-link">Register</a></p>
                </div>
            </form>
        </div>

        <div class="form-box register">
            <h2>Registration</h2>
            <form action="_actions/create.php" method="post" autocomplete="off">
                <div class="input-box">
                    <span class="icon"><ion-icon name="person"></ion-icon></span>
                    <input type="text" name="name" placeholder=" " required>
                    <label>Username</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="email" name="email" placeholder=" "required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                      <span class="icon lock-icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <span class="icon eye-toggle" style="display: none; cursor: pointer;">
                        <ion-icon name="eye"></ion-icon>
                    </span>
                    <input type="password" name="password" placeholder=" " required>
                    <label>Password</label>
                </div>
                 <div class="input-box">
                      <span class="icon lock-icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <span class="icon eye-toggle" style="display: none; cursor: pointer;">
                        <ion-icon name="eye"></ion-icon>
                    </span>
                    <input type="password" name="confirm_password" placeholder=" " required>
                    <label>Confirm Password</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox" required>I agrees to the terms and conditions </label>
                </div>
                <button type="submit" class="btn">Register</button>
                <div class="login-register">
                    <p>Already Have an Account? <a href="#" class="login-link">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
