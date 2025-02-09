<?php
session_name("greenleaf");
session_start();

// Set session lifetime to 1 hour (3600 seconds)
ini_set('session.gc_maxlifetime', 3600); 
session_set_cookie_params(3600); // Ensure the session cookie also expires after 1 hour

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['login_email'];
    $password = $_POST['your_pass'];

    // Read the users.json file
    $file = 'database/users.json';
    if (file_exists($file)) {
        $users = json_decode(file_get_contents($file), true);
    } else {
        echo "<script>
                alert('User list not found!');
                window.location.href = 'index.php';
              </script>";
        exit();
    }

    // Check the user account
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            // Verify the hashed password
            if (password_verify($password, $user['password'])) { // Compare entered password with the hashed password
                // Store user information in the session
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['idtele'] = $user['idtele'];
                $_SESSION['province'] = $user['province'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['fullname']; // Assuming 'fullname' exists in the user data

                // Record the session start time
                $_SESSION['login_time'] = time();

                // Redirect based on the user's role
                if ($user['role'] == 'quan_ly') {
                    header("Location: quan_ly");
                } elseif ($user['role'] == 'mua_hang') {
                    header("Location: pages");
                }
                 elseif ($user['role'] == 'ke_toan_kho') {
                    header("Location: pages");
                }
                exit();
            } else {
                echo "<script>
                        alert('Incorrect password!');
                        window.location.href = 'index.php';
                      </script>";
                exit();
            }
        }
    }

    echo "<script>
            alert('Account not found!');
            window.location.href = 'index.php';
          </script>";
}
?>
