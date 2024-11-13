<?php
session_start();
session_unset();
session_destroy();
header('Location: /SGAC/Public/welcome.html');
exit();
?>

