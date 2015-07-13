<?php
    session_start();

    // connect to database
    $mysqli = new mysqli("REDACTED", "REDACTED", "REDACTED", "REDACTED");
    if ($mysqli->connect_errno) {
        exit("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
    }

    // handle POST submission
    if (isset($_POST['submit'])) {
        $submissionSuccessful = false;
        $dbPictureID     = $_POST['pictureID'];
        $dbSymbol        = $_POST['symbol'];
        $dbDuration      = $_POST['duration'];
        $dbDurationInput = $_POST['durationInput'];
        $dbPitch         = $_POST['pitch'];
        $dbPitchInput    = $_POST['pitchInput'];
        $dbAccidental    = $_POST['accidental'];
        $dbLevel         = $_POST['level'];
        $dbDotted        = isset($_POST['dotted']) ? true : false;
        $dbIP            = $_SERVER['REMOTE_ADDR'];

        if ($dbSymbol === "note") {
            if ($dbDuration !== "none" && $dbPitch !== "none") {
                submitNote($dbPictureID, $dbSymbol, $dbDuration, $dbDurationInput, $dbPitch, $dbPitchInput, $dbAccidental, $dbDotted, $dbIP, $dbLevel, $mysqli);
                $submissionSuccessful = true;
            }
        } elseif ($dbSymbol === "pause") {
            if ($dbDuration !== "none") {
                submitPause($dbPictureID, $dbSymbol, $dbDuration, $dbDurationInput, $dbDotted, $dbIP, $dbLevel, $mysqli);
                $submissionSuccessful = true;
            }
        }

        // "post redirect get" pattern
        // avoids multiple submission after browser refresh
        $_SESSION['submitted'] = $submissionSuccessful;
        header("HTTP/1.1 303 See Other");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // set level
    if (isset($_COOKIE['level'])) {
        $level = intval($_COOKIE['level']);
        $answers = intval($_COOKIE['answers']);
    } else {
        $level = 1;
        $answers = 0;
    }

    // increase answer count if last submission was a succussful one
    if ($_SESSION['submitted'] == true) {
        $_SESSION['submitted'] = false;
        $answers++;
    }
    
    // if next level reached:
    $answerMultiplicator = 3;
    if ($answers >= $level*$answerMultiplicator) {
        $level++;
        $answers = 0;
    }

    // set cookies
    setcookie("level", $level, time()+30*24*60*60);
    setcookie("answers", $answers, time()+30*24*60*60);

    // delete cookies
    // setcookie("level", 1, time()-1);
    // setcookie("answers", 0, time()-1);

    // set additional values for html
    $pointsUntilNextLevel = $level*$answerMultiplicator - $answers;
    $pictureID = getRandomPictureID($mysqli); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>RecordLabel</title>

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/custom-styles.css" rel="stylesheet">

    <script src="js/jquery-2.1.4.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/custom-scripts.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <h1>RecordLabel <small>by&nbsp;Pascal Attwenger</small></h1>
            <p class="lead <?php if ($level > 1) echo 'hidden' ?>">Für meine Bachelor-Arbeit der Informatik im Bereich Machine Learning benötige ich große Mengen an richtig gelabelten Noten.
            Setze also dein mühsam erlerntes Wissen über Musiknotation endlich für etwas Nützliches ein, hilf mir zum Titel des BSc und gewinne nebenbei tolle Preise. Danke!<br>
            <small>Disclaimer: Gewinne sind lediglich sentimental-digitaler Natur und können nicht in reale Produkte umgetauscht werden.</small><br>
            <small>PS: Den Violinschlüssel davor müsst ihr euch leider vorstellen, das war technisch so nicht möglich.</small></p>
            <p>Danke an alle für eure Mithilfe! Die Ergebnisse könnt ihr <a href="../bachelor/thesis.pdf" target="_blank">hier</a> nachlesen.</p>
        </header>

        <main class="jumbotron">
            <div class="row">
                <div class="col-sm-12">
                    <h2>Level <?= $level ?><br><small>Noch <?= $pointsUntilNextLevel ?> Fragen bis Level <?= $level+1 ?></small></h2>
                    <h3>Was ist das?</h3>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <img src="img/png-from-db.php?id=<?= $pictureID ?>" alt="Note" class="img-thumbnail" style="height: 180px; width: auto">
                </div>

                <div class="note-description col-sm-8">
                    <form method="post" class="form-horizontal">
                        <input type="hidden" name="pictureID" value="<?= $pictureID ?>">
                        <input type="hidden" name="level" value="<?= $level ?>">
                        <div class="form-group">
                            <div class="btn-group col-sm-12" data-toggle="buttons">
                                <label class="btn btn-primary active">
                                    <input type="radio" name="symbol" value="note" autocomplete="off" checked> Note
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" name="symbol" value="pause" autocomplete="off"> Pause
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6">
                                <select name="duration" id="durationSelect" class="form-control">
                                    <option value="none">Dauer?</option>
                                    <option value="whole">Ganze</option>
                                    <option value="half">Halbe</option>
                                    <option value="quarter">Viertel</option>
                                    <option value="eighth">Achtel</option>
                                    <option value="sixteenth">Sechzehntel</option>
                                    <option value="other">andere:</option>
                                </select>
                            </div>

                            <div class="checkbox col-sm-6">
                                <label>
                                    <input type="checkbox" name="dotted"> Punktiert
                                </label>
                            </div>

                            <div class="col-sm-12" id="otherDuration">
                                <input type="text" name="durationInput" placeholder="Dauer:" class="form-control">
                            </div>
                        </div>

                        <div class="form-group" id="pitchInputs">
                            <div class="col-sm-6">
                                <select name="pitch" id="pitchSelect" class="form-control">
                                    <option value="none">Ton?</option>
                                    <option>c3</option>
                                    <option>h2</option>
                                    <option>a2</option>
                                    <option>g2</option>
                                    <option>f2</option>
                                    <option>e2</option>
                                    <option>d2</option>
                                    <option>c2</option>
                                    <option>h1</option>
                                    <option>a1</option>
                                    <option>g1</option>
                                    <option>f1</option>
                                    <option>e1</option>
                                    <option>d1</option>
                                    <option>c1</option>
                                    <option>h</option>
                                    <option>a</option>
                                    <option value="other">andere:</option>
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-primary active">
                                        <input type="radio" name="accidental" value="natural" autocomplete="off" checked> ♮
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="accidental" value="sharp" autocomplete="off"> ♯
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="accidental" value="flat" autocomplete="off"> ♭
                                    </label>
                                </div>
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#helpModal">
                                    <span class="glyphicon glyphicon-question-sign"></span>
                                </button>
                            </div>

                            <div class="col-sm-12" id="otherPitch">
                                <input type="text" name="pitchInput" placeholder="Ton:" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" name="submit" class="btn btn-success">Weiter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <div class="text-right">
            <a href="faqs.php" target="_blank">FAQs</a> | <a href="contact.php" target="_blank">Kontakt</a>
        </div>
    </div>



    <div class="modal fade" id="helpModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Hilfe: Notennamen</h4>
                </div>
                <div class="modal-body">
                    <img src="img/pitch-help.gif" class="img-responsive center-block">
                </div>
            </div>
        </div>
    </div>

<?php
    if ($level > 1 && $answers === 0) {
        $levelUpValues = getLevelUpValues($level, $mysqli);
        // print modal:
?>
    <div class="modal fade" id="levelUpModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3 class="modal-title">Level <?= $level ?>: Du gewinnst <?= $levelUpValues['title'] ?></h3>
                </div>
                <div class="modal-body">
                    <p><img src="img/levels/<?= $levelUpValues['image'] ?>" class="img-responsive img-rounded center-block" style="max-height: 400px" alt="<?= $levelUpValues['alt'] ?>"></p>
                    <p><strong><?= $levelUpValues['description'] ?></strong></p>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
<?php
    } // end if
?>

</body>
</html>

<?php
    $mysqli->close();

    function getRandomPictureID($mysqli) {
        // choose picture with different algorithm in 1 of X cases
        $x = 3;
        $random = mt_rand(1, $x);
        
        if ($random === 1) {
            // gets one of the picture ids with no submissions
            $res = $mysqli->query("SELECT picture_id FROM picture_no_entries2 ORDER BY RAND() LIMIT 1");
            if ($row = $res->fetch_assoc()) {
                echo "<!-- zero -->";
                return $row['picture_id'];
            } else {
                $res = $mysqli->query("SELECT picture_id FROM random_picture");
                $row = $res->fetch_assoc();
                echo "<!-- random -->";
                return $row['picture_id'];
            }
        } elseif ($random === 2) {
            // gets one of the picture ids with only one submission
            $res = $mysqli->query("SELECT picture_id FROM picture_single_entries2 ORDER BY RAND() LIMIT 1");
            if ($row = $res->fetch_assoc()) {
                echo "<!-- one -->";
                return $row['picture_id'];
            } else {
                $res = $mysqli->query("SELECT picture_id FROM random_picture");
                $row = $res->fetch_assoc();
                echo "<!-- random -->";
                return $row['picture_id'];
            }
        } else {
            // gets completely random picture id
            $res = $mysqli->query("SELECT picture_id FROM random_picture");
            $row = $res->fetch_assoc();
            echo "<!-- random -->";
            return $row['picture_id'];
        }
    }

    function submitNote($pictureID, $symbol, $duration, $durationInput, $pitch, $pitchInput, $accidental, $dotted, $ip, $level, $mysqli) {
        $durationInput = ($duration === "other") ? $durationInput : NULL;
        $pitchInput = ($pitch === "other") ? $pitchInput : NULL;

        $query = "INSERT INTO submissions (picture_id, symbol, duration, duration_input, pitch, pitch_input, accidental, dotted, ip, level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("issssssisi", $pictureID, $symbol, $duration, $durationInput, $pitch, $pitchInput, $accidental, $dotted, $ip, $level);
        $stmt->execute();
        if ($stmt->error) printf("Error: %s.\n", $stmt->error);
    }

    function submitPause($pictureID, $symbol, $duration, $durationInput, $dotted, $ip, $level, $mysqli) {
        $durationInput = ($duration === "other") ? $durationInput : NULL;

        $query = "INSERT INTO submissions (picture_id, symbol, duration, duration_input, dotted, ip, level) VALUES (?, ?, ?, ?, ?, ?, ?);";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("isssisi", $pictureID, $symbol, $duration, $durationInput, $dotted, $ip, $level);
        $stmt->execute();
        if ($stmt->error) printf("Error: %s.\n", $stmt->error);
    }

    function getLevelUpValues($level, $mysqli) {
        $values = array();
        $result = $mysqli->query("SELECT * FROM levels WHERE number = $level");
        if ($entry = $result->fetch_assoc()) {
            $values['title']       = $entry['title'];
            $values['alt']         = $entry['alt'];
            $values['description'] = $entry['description'];
            $values['image']       = $entry['image'];
        } else {
            // fallback values if no more db entries
            $values['title']       = "meine tiefste Dankbarkeit!";
            $values['alt']         = "Dankbarkeit";
            $values['description'] = "Jetzt und bis in alle Ewigkeit :)";
            $values['image']       = "danke.png";
        }
        return $values;
    }
?>