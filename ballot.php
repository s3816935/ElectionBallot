<?php require_once('vote_function.php');?>
<?php
    session_start(); 
    $voterID = $_SESSION['VoterID'];
    $electorate = $_SESSION['Electorate'];
    $candidates = array(); 
    $candidates = findCandidates($electorate);
    // print_r($candidates); 
    // print_r($candidates["CANDIDATEID"]); 
    $length = count($candidates["CANDIDATE"]);
    $candidateIDs = []; 
    $candidateIDs = $candidates["CANDIDATEID"];
    
    // echo $candidateIDs[1];
    // echo $length;
    if(isset($_POST['submit'])) {
        $message = processVote($voterID, $electorate, $candidateIDs, $_POST["preference"]);
        echo $message;
        // print_r($candidates["CANDIDATEID"]);
        // print_r($_POST["preference"]);
        // $num = count($candidateIDs); 
        // $num1 = count($_POST["preference"]); 
        // echo $num;
        // echo $num1;
        // print_r($_POST['preference']);
        // $preferences = $_POST['preference'];
        // foreach ($preferences as $preference) {
        //     echo $preference;
        // }
        exit();
        // echo $_POST["candidate"][0]
        // // for ($i=0; $i < $length; $i++) {
        // //     echo $_POST[$i];
        // //     exit();
        // // }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <link rel="stylesheet" type="text/css" href="style1.css">
    <main>
        <header>
            
        </header>
        <body>
            <div class="content">
                <h1>Electorial Division of <?php echo $electorate ?></h1>
                <hr />
                <br>
                <br>Number the boxes from 1 to <?=$length?> in the order of your choice <br>
                <form method="post">
                    <?php for ($i=0; $i < $length; $i++) { ?>
                        <br><input class="input-candidate" type="text" name="preference[]">
                            <label class="candidate"><?=$candidates["CANDIDATE"][$i] ?></label>
                            <label class="party">(<?=$candidates["PARTY"][$i] ?>)</label><br>
                        </input>
                        
                    <?php } ?>
                    <br><button type="submit" name="submit" value="submit">Vote</button>
                </form>
                
            </div>
            
        </body>
    </main>

</html>