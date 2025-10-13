<?php
    define("TOOLKIT_PATH", '/var/www/html/');
    require_once(TOOLKIT_PATH . '_toolkit_loader.php');

    session_start();
    $html_head =<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <title>SAML Authentication Tester</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/bootstrap.min.css"> <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="css/main.css"> 

</head>
<body>
  <div class="container">
        <div class="fancy-box">
HTML;
    $auth = new OneLogin\Saml2\Auth();


    if (isset($_REQUEST['acs'])) {
        // Process the SAML response
        $auth->processResponse();
        $errors = $auth->getErrors();
        echo $html_head;
        if (!empty($errors)) {
            echo "<h1 class=\"fancy-title\">Problem Content</h1>";
            error_log("SAML Response processing errors: " . implode(', ', $errors));
            echo '<p style="color:red;">Error: ' . implode(', ', $errors) . '</p>';
            exit();
        }
        if (!$auth->isAuthenticated()) {
            echo "<h1 class=\"fancy-title\">Not Authenticated</h1>";
            echo '<p style="color:red;">Not authenticated</p>';
            exit();
        }
        ?>

          <h1 class="fancy-title">Secured Content</h1>
          <p class="description">If you are seeing this, you were authenticated successfully.</p>
          <form method="POST" action="auth.php">
            <input type="submit" name="logout" value="Logout" class="fancy-button">
          </form>
          <br>
          <?php
          $user = $auth->getNameId();
          $_SESSION['user'] = $user;
          echo "<h3>SSO Username: <b>$user</b></h3>";

          // Get the user's attributes
        $attributes = $auth->getAttributes();

        echo "<h2>Attributes Returned by SSO</h2>";
        echo "<table>";
        echo "<tr><th>Attribute</th><th>Value</th></tr>";

        // Print all the attributes in a table
        foreach ($attributes as $key => $value) {
        if (is_array($value)) {
            // If the value is an array, convert it to a comma-separated string with brackets around it
            $value = '[' . implode(', ', $value) . ']';
        }
        if (is_null($value)) {
            $value = 'NULL';
        }
            echo "<tr><td>$key</td><td>$value</td></tr>";
        }

        echo "</table>";
      } else {
        if (isset($_POST['logout'])) {
            // Handle logout
            $auth->logout();
            session_destroy();
            exit();
        }
        try {
            error_log("User not authenticated, initiating SAML login.");
            $auth->login();
        } catch (Exception $e) {
            error_log("Error during SAML login: " . $e->getMessage());
            echo $html_head;
            echo '<p style="color:red;">Error: ' . $e->getMessage() . '</p>';
            exit();
        }
      }

      ?>
      </div>
    </div>
  </body>
  </html>
