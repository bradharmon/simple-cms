<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/session.php');
include(CMS_DIR . '/login/functions.php');

$email = $_POST['email'];
$password = $_POST['password'];
$resend = $_GET['resend'];
$resend_email = $_GET['email'];

//login with provided credentials
if(isset($_POST['email']) && isset($_POST['password']))
{
   //verify password
   try {
      $sth = $dbh->prepare("SELECT password, salt FROM users WHERE email = ?");
      $sth->execute(array($email));
      
      if($sth->rowCount() < 1)
         push_error("Invalid Email address or Password.");
      die_on_error("login.php");
   
      $userData = $sth->fetch(PDO::FETCH_ASSOC);
   } catch(PDOException $e) {
      echo $e->getMessage();
      die();
   }
   
   $hash = crypt($password, "$2a$12$" . $userData['salt'] . "$");
   if($hash != $userData['password']) //incorrect password
   {
      push_error("Invalid Email address or Password.");
      die_on_error("login.php");
   }
   else
   {
      validateUser($email);
   }
   
   //Check if user credentials have been verified by clicking on link in email
   try {
      $sth = $dbh->prepare("SELECT verified FROM users WHERE email = ?");
      $sth->execute(array($email));
      list($verified) = $sth->fetch(PDO::FETCH_NUM);
   } catch (PDOException $e) {
      echo $e->getMessage();
   }
   if($verified === '0')
   {
      $link = "<a href=\"". CMS_URL ."/login.php?email=$email&resend=true\">here</a>";
      push_error("Your registration has not yet been verified. Please check your email and click the verification link.<br>");
      push_error("Click $link to resend link<br>");
   }
   die_on_error("login.php");
   
   //redirect to main web page
   header('Location: '. WEBSITE_URL . '/index.php');
   die();
}
//resend verification link if needed
else if($resend === 'true' && $resend_email !== '')
{
   //lookup email and verification hash
   try {
      $sth = $dbh->prepare("SELECT email, verification_hash FROM users WHERE email = ?");
      $sth->execute(array($resend_email));
   list($resend_email, $verification_hash) = $sth->fetch(PDO::FETCH_NUM);
   } catch (PDOException $e){
      echo $e->getMessage();
      die();
   }

   //resend email
   $link = CMS_URL . "/verify.php?email=$resend_email&code=$verification_hash";
   $message = "Thank you for signing up for access to simple-cms.
   If you have received this email in error, please disregard it. Otherwise, please click on the link below to activate your account
   $link";
   if(!mail($resend_email, "Signup Verification", $message))
      push_error("Could not send email.  Please contact the website administrator for support.");
   die_on_error("register.php");

   push_status("Verification email resent.");
   header('Location: login.php');
   die();
}

//if we aren't checking login credentials, or resending verification link
//display the login form
//print_session_vars();
?>

<html>
<head>
<link rel="stylesheet" href="login.css" type="text/css"/>
</head>

<body>

<div id="login-wrapper">
<?php print_session_vars() ?>
<div id="login">
<form name="login" action="login.php" method="post">
    <div class="clear">
       <label for="email">Email:</label>
       <input type="text" name="email" />
    </div>

    <div class="clear">
       <label for="password">Password:</label>
       <input type="password" name="password" />
    </div>
    <div class="clear">
       <input class="submit" type="submit" value="Login" />
       <span><strong>or</strong> <a href="<?php echo CMS_URL . '/reset_password.php' ?>">Reset your password</a></span>
    </div>
</form>
</div>
</div>

<body>
</html>
