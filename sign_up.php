<?php
   ob_start();
   session_start();
?>

<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <title>Money Tacker</title>
   </head>
   <body>
      <div class = "container">
         <?php
            include ("connect.php");

            function startSessionForUser($userId, $full_name, $email) {
               $_SESSION['valid'] = true;
               $_SESSION['timeout'] = time();
               // Ending a session in 30 minutes from the starting time.
               $_SESSION['expire'] = $_SESSION['start'] + (30 * 60);
               $_SESSION['user_id'] = $userId;
               $_SESSION['username'] = $full_name;
               $_SESSION['email'] = $email;
            }

            function isUserExists($conn, $email) {
               $sql = "SELECT * FROM users
                     WHERE email='".$email."';";
               return $conn->query($sql)->fetchColumn() > 0;
            }

            function signUp($conn, $full_name, $email, $password, $confirm_password) {
               $error = "";

               if (strlen($full_name) == 0) {
                  $error = "Full name can't be empty.";
               }
               if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                  $error = "Email is invalid.";
               }
               if (strlen($password) < 6 || $password != $confirm_password) {
                  $error = "Passwords must be equal and length > 5.";
               }
               if (isUserExists($conn, $email)) {
                  $error = "User with such email already exists.";
               }

               if ($error == "") {
                  $createUserSql = "INSERT INTO users (created_at, full_name, email, password)
                              VALUES (".time().", '".$full_name."', '".$email."', '".$password."');";
                  $conn->exec($createUserSql);
                  startSessionForUser($conn->lastInsertId(), $full_name, $email);
                  header('Location: '.'index.php', true, $permanent ? 301 : 302);
               } else {
                  header('Location: '.'error.php?error='.$error, true, $permanent ? 301 : 302);
               }
               exit();
            }
            
            if (isset($_POST['sign_up'])) {
               try {
                  $conn = new PDO("mysql:host=$host;dbname=$dbname", $login, $password);
                  // set the PDO error mode to exception
                  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                  signUp($conn, $_POST['full_name'], $_POST['email'], $_POST['password'], $_POST['confirm_password']);
               } catch(PDOException $error) {
                  echo "<p>Error: ".$error->getMessage()."</p>\n";
               }
            }
         ?>
      </div>
      
      <div class = "container">
         <form action="sign_up.php" method="post">
            <fieldset>
               <input type="hidden" name="action" value="action_sign_up" />
               <p><input type="text" name="full_name" size="40" maxlength="40" placeholder="Full name" /></p>
               <p><input type="email" name="email" size="40" maxlength="40" placeholder="Email" /></p>
               <p><input type="password" name="password" size="40" maxlength="40" placeholder="Password" /></p>
               <p><input type="password" name="confirm_password" size="40" maxlength="40" placeholder="Confirm password" /></p>
               <p><input type="submit" name="sign_up" value="Sign Up" /></p>
               <a href="sign_in.php">Already have an account? Sign In</a>
            </fieldset>
         </form>
      </div> 
   </body>
</html>