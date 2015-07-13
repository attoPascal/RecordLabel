<pre>
<?php
	$mysqli = new mysqli("REDACTED", "REDACTED", "REDACTED", "REDACTED");
	if ($mysqli->connect_errno) {
	    exit("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
	}

	$dirname = "files/";
	$filecount = count(glob($dirname . "*"));
	echo "$filecount files\n\n";

	if ($dir = opendir($dirname)) {
	    $i = 1;
	    while (false !== ($filename = readdir($dir))) {
	    	$path = "./" . $dirname . $filename;
	        if (is_file($path)) {
	            echo "File $i of $filecount\n";

	            $size = getimagesize($path);
	            $width = $size[0];
	            $height = $size[1];
	            $type = $size["mime"];

	            if ($type == "image/png") {
	            	$data = $mysqli->real_escape_string(file_get_contents($path));
	            	$mysqli->query("INSERT INTO pictures(data, width, height, type) VALUES('$data', '$width', '$height', '$type');");
	            	echo "saved to db\n";

	            	unlink($path);
	            	echo "file deleted\n\n";
	            } else {
	            	echo "no png; file skipped\n\n";
	            }

	            $i++;
	        }
	    }
    	closedir($dir);
    } else {
    	echo "Could not open directory";
    }

    $mysqli->close();


// 	if (!$mysqli->query("DROP TABLE IF EXISTS test") ||
// 	    !$mysqli->query("CREATE TABLE test(id INT)") ||
// 	    !$mysqli->query("INSERT INTO test(id) VALUES (1), (2), (3)")) {
// 	    echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
// 	}

// 	$res = $mysqli->query("SELECT id FROM test ORDER BY id ASC");

// 	echo "Reverse order...\n";
// 	for ($row_no = $res->num_rows - 1; $row_no >= 0; $row_no--) {
// 	    $res->data_seek($row_no);
// 	    $row = $res->fetch_assoc();
// 	    echo " id = " . $row['id'] . "\n";
// 	}

// 	echo "Result set order...\n";
// 	$res->data_seek(0);
// 	while ($row = $res->fetch_assoc()) {
// 	    echo " id = " . $row['id'] . "\n";
// 	}
?>
</pre>