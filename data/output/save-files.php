<pre>
<?php
	$mysqli = new mysqli("REDACTED", "REDACTED", "REDACTED", "REDACTED");
	if ($mysqli->connect_errno) {
	    exit("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
	}

    $res = $mysqli->query("SELECT COUNT(id) FROM pictures");
    $row = $res->fetch_row();
    $num_pics = $row[0];

	$stmt  = $mysqli->prepare("SELECT symbol, duration, pitch FROM submissions WHERE picture_id = ?");
	// $stmt2 = $mysqli->prepare("SELECT data FROM pictures WHERE id = ?");

	$exported = 0;

	// picture loop
	for ($i = 1; $i <= $num_pics; $i++) {
		echo "pic $i of $num_pics:\t";
		$stmt->bind_param("i", $i);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($symbol, $duration, $pitch);
		
		$num_submissions = $stmt->num_rows();

		$results = array(
			"symbol" => array(),
			"duration" => array(),
			"pitch" => array()
		);

    	// submission loop
    	while ($stmt->fetch()) {
    		increaseCount($results["symbol"], $symbol);
    		increaseCount($results["duration"], $duration);
    		increaseCount($results["pitch"], $pitch);
    	}

		// $stmt2->bind_param("i", $i);
		// $stmt2->execute();
		// $stmt2->bind_result($data);
		// $stmt2->fetch();

    	if ($num_submissions >= 2) {
    		// when >= 3: at least 60% of submissions must agree
    		// when 2: both must agree
    		$threshold = ($num_submissions == 2) ? 2 : ($num_submissions * 0.6);

    		$prediction = array();
    		getPrediction($prediction, $results["symbol"], "symbol", $threshold);
    		getPrediction($prediction, $results["duration"], "duration", $threshold);
    		getPrediction($prediction, $results["pitch"], "pitch", $threshold);

    		if (count($prediction) == 3) {
    			$filename = getFilename($prediction, $i);

    			$res = $mysqli->query("SELECT data FROM pictures WHERE id = $i");
    			$row = $res->fetch_row();
    			$data = $row[0];

    			if (saveFile($filename, $data)) {
    				echo "$filename saved";
    				$exported++;
    			} else {
    				echo "could not save file";
    			}
    		} else {
    			echo "skipped: inconclusive submissions";
    		}
    	} else {
    		echo "skipped: not enough submissions";
    	}

    	echo "\n";
    }

    echo "$exported files exported";

    $stmt->close();
	$mysqli->close();

	function increaseCount(&$array, $key) {
		if (array_key_exists($key, $array)) {
			$array[$key] += 1;
		} else {
			$array[$key] = 1;
		}
	}

	function getPrediction(&$prediction, $array, $name, $threshold) {
		foreach ($array as $key => $value) {
			if ($value >= $threshold) {
				$prediction[$name] = $key;
			}
		}
	}

	function getFilename($prediction, $id) {
		if ($prediction["symbol"] == "note") {
			return "note-" . $prediction["duration"] . "-" . $prediction["pitch"] . "-$id";
		} elseif ($prediction["symbol"] == "pause") {
			return "rest-" . $prediction["duration"] . "-$id";
		} else {
			return NULL;
		}
	}

	function saveFile($filename, $data) {
		$dirname = "files/";
		return file_put_contents($dirname.$filename.".png", $data);
	}
?>
</pre>