<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/session.php');
include(CMS_DIR . '/login/functions.php');

//password change form filled out from emailed link
if(isset($_POST['email']) && isset($_POST['code']) && isset($_POST['pass1']) && isset($_POST['pass2']))
{
   $email = $_POST['email'];
   $verification_hash = $_POST['code'];
   $pass1 = $_POST['pass1'];
   $pass2 = $_POST['pass2'];

   //check passwords match
   if($pass1 !== $pass2)
      push_error("Passwords do not match!");
   //don't use escaped email/code because then the url could have double escaped versions sent back out
   //in the url
   die_on_error(CMS_URL . "/reset_password.php?email=" . $_POST['email'] . "&code=" . $_POST['code']);

   //calculate new password hash
   if(CRYPT_BLOWFISH == 1)
   {
      $salt = substr(createSalt(),0,22);
      $hash = crypt($pass1, "$2a$12$" . $salt . "$");
   }
   else
      push_error("Cannot Change Password! CRYPT_BLOWFISH not installed in this version of php<br>");
   die_on_error("login.php");

   try {
      //change the password
      $sth = $dbh->prepare("UPDATE users SET password = ?, salt = ? WHERE email = ? AND verification_hash = ?");
      $sth->execute(array($hash, $salt, $email, $verification_hash));
   
      if($sth->rowCount() < 1)
      {
         push_error("Unable to reset password. Please try again.");
      }
      die_on_error(CMS_URL . "/reset_password.php?email=" . $_POST['email'] . "&code=" . $_POST['code']);
   
      //clear out verification hash
      $sth = $dbh->prepare("UPDATE users SET verification_hash = '' WHERE email = ?");
      $sth->execute(array($email));
   } catch (PDOException $e) {
      echo $e->getMessage();
   }

   push_status("Password successfully changed.");
   header('Location: login.php');
   die();
}
//reset form filled out via website, process reset request
else if(isset($_POST['email']))
{
   try{
      //make sure email exists
      $email = $_POST['email'];
      $sth = $dbh->prepare("SELECT email FROM users WHERE email = ?");
      $sth->execute(array($email));
      list($email) = $sth->fetch(PDO::FETCH_NUM);
      if($email !== '')
      {
         //the email is in the db, so let's create a verification key
         //and send it to their email address
         $verification_hash = substr(createSalt(), 0, 13);
   
         $link = CMS_URL . "/reset_password.php?email=$email&code=$verification_hash";
         $message = "Clicking the link below will take you to a page that will allow you to change your password.
         If you have received this email in error, please disregard it. Otherwise, please click on the link below to activate your account
         $link";
   
         //add the verification hash to the db
	 $sth = $dbh->prepare("UPDATE users SET verification_hash = ? WHERE email = ?");
	 $sth->execute(array($verification_hash, $email));

         //send them an email
         if($sth->rowCount())
         {
            if(mail($email, "Password Reset Verification", $message))
            {
               push_status("Please check your email for a verification link to change your password.");
               header("Location: login.php");
               die();
            }
            else
            {
               push_error("Could not send verification email to reset your password. Please contact your system adinistrator.");
               die_on_error("login.php");
            }
         }
         //email not found in db
         else
         {
            push_error("Cannot reset password.");
            die_on_error("login.php");
         }
      }
   } catch (PDOException $e) {
      echo $e->getMessage();
   }

   cleanup_session();
   die();
}
//reset link clicked in email, display password reset form with email already filled in
else if(isset($_GET['email']) && isset($_GET['code']))
{
   session_start();

   $email = $_GET['email'];
   $verification_hash = $_GET['code'];

   //display password reset form
//   $form = "<form name=\"reset\" action=\"reset_password.php\" method=\"post\">\n" .
//           "   Email: $email<br>\n" .
//	   "   <input type=\"hidden\" name=\"email\" value=\"$email\" />\n" .
//	   "   <input type=\"hidden\" name=\"code\" value=\"$verification_hash\" />\n" .
//           "   Password: <input type=\"text\" name=\"pass1\" />\n" .
//           "   Password Again: <input type=\"text\" name=\"pass2\" />\n" .
//           "   <input type=\"submit\" value=\"Submit\" />\n" .
//           "</form>\n";

   $html = '<html>' .
           '<head>' .
           '<link rel="stylesheet" href="login.css" type="text/css"/>' .
           '</head>' .
           '<body>' .
           '<div id="login-wrapper">' .
           print_session_vars() .
           '<div id="login">' .
           '<form name="reset" action="reset_password.php" method="post">' .
           '    <div class="clear">' .
           '       <label for="email">Email:</label>' .
           '       <input type="email" name="email" value="' . $email . '" disabled/>' .
           '    </div>' .
           '    <div class="clear">' .
           '       <label for="password">Password:</label>' .
           '       <input type="password" name="pass1" />' .
           '    </div>' .
           '    <div class="clear">' .
           '       <label for="password">Password:</label>' .
           '       <input type="password" name="pass2" />' .
           '    </div>' .
           '    <div class="clear">' .
	   '       <input type="hidden" name="code" value="' . $verification_hash . '"' .
           '       <input class="submit" type="submit" value="Reset" />' .
           '    </div>' .
           '</form>' .
           '</div>' .
           '</div>' .
           '<body>' .
           '</html>';

   echo $html;
   die();
}
//if no post or get vars, print the form
push_status("Enter your email address that you signed up with.");
push_status("An email will be sent to this address with further instructions.");
?>
<html>
<head>
<link rel="stylesheet" href="login.css" type="text/css"/>
</head>

<body>

<div id="login-wrapper">
<?php print_session_vars() ?>
<div id="login">
<form name="reset" action="reset_password.php" method="post">
    <div class="clear">
       <label for="email">Email:</label>
       <input type="text" name="email"/>
    </div>

    <div class="clear">
       <input class="submit" type="submit" value="Reset" />
    </div>
</form>
</div>
</div>

<body>
</html>
