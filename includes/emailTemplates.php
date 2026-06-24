<?php
function welcomeEmailTemplate($email) {
    return "
        <div style='padding: 10px'>
            <h3>Welcome to WakaAbout</h3>
            <p>Hi $email,</p>
            <p>Thanks for subscribing to our newsletter!</p>
            <p>You’ll receive weekly updates and stories.</p>
            <a href=https://wakaabout.net
   style= 'width:200px; text-align:center; background:red;  color:#fff;padding:5px 1px; border-radius:10; text-decoration:none;display:block;'>
   Explore Wakaabout
</a>
            <br>
            <p style=margin-top:2%>— WakaAbout Team</p>
        </div>
    ";
}
function resetPasswordEmailTemplate($resetLink) {
    return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px; background-color: #ffffff;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #333333; margin: 0; font-size: 24px;'>Waka<span style='color: #ff3b30;'>about</span></h2>
            </div>
            <div style='color: #555555; line-height: 1.6;'>
                <h3 style='color: #333333;'>Reset Your Password</h3>
                <p>Hello,</p>
                <p>We received a request to reset the password for your account. Click the button below to set a new password:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$resetLink' style='background-color: #ff3b30; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>Reset Password</a>
                </div>
                <p>This password reset link will expire in 30 minutes.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
            </div>
            <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
            <p style='font-size: 12px; color: #999999; text-align: center;'>&copy; 2026 WakaAbout. All rights reserved.</p>
        </div>
    ";
}