<?php
	header("Content-type: image/png");

	$mysqli = new mysqli("REDACTED", "REDACTED", "REDACTED", "REDACTED");
	$stmt = $mysqli->prepare("SELECT data FROM pictures WHERE id = ?");

	$id = $_GET["id"];
	$stmt->bind_param("i", $id);
	$stmt->execute();
    $stmt->bind_result($image);

    if ($stmt->fetch()) {
        echo $image;
    }

    $stmt->close();
	$mysqli->close();
?>