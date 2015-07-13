<?php
	if ($_GET['code'] === "gustav") {
		setcookie("level", "11", time()+30*24*60*60);
    	setcookie("answers", "1", time()+30*24*60*60);
    }
    echo "Cheater!"
?>