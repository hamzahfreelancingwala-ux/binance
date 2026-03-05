<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<body>
    <script>
        // Smooth JS redirection to homepage
        window.location.href = "index.php";
    </script>
</body>
</html>
