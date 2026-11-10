<?php
session_start();
unset($_SESSION['sec_user_id']);
unset($_SESSION['sec_username']);
unset($_SESSION['sec_fullname']);
unset($_SESSION['sec_role']);
session_destroy();
header('Location: login.php');
exit;
