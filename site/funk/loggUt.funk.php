<?php
//Logger ut og sender til innloggingssside
session_start();
session_destroy();
header("Location: ./login.funk.php");

?>