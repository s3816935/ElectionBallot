<?php require_once('vote_function.php');?>
<?php
    session_start();
    if(isset($_POST['submit'])) {
        if(isset($_POST["voted"])) {
            echo '<script language="javascript">';
            echo 'alert("You have already voted in this election, you cannot vote again! ") ';
            echo '</script>';
        }
        else {
            $errors = [];
            $errors = validateVoter($_POST);
            if($errors[0] === 0) {
                // echo "You can vote"; 
                // echo $errors[1]; 
                // echo $errors[2]; 
                $_SESSION['VoterID'] = $errors[1];
                $_SESSION['Electorate'] = $errors[2];
                header("Location: ballot.php");
                // exit();
            }
            else {
                echo "<script>alert('" . $errors[0] . "');</script>";
            }
        }
        // $errors = loginUser($_POST);
        // if(count($errors) === 0)
        //     redirect('myServices.php');
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
                <h1>Electoral Role Search</h1>
                <hr />
                <br>
                <form method="post">
                    Full Name <br><input class="input-voter" type="text" placeholder="Enter your full name..." name="full_name"><br><br>
                    Address <br><input class="input-voter" type="text" placeholder="Street number and street name..." name="address"><br><br>
                    Appartment Suit, etc (optional) <br><input class="input-voter" type="text" name="appartment"><br><br>
                    Suburb <br><input class="input-voter" type="text" placeholder="Suburb..." name="suburb"><br><br>
                    State <br><input class="input-voter" type="text" placeholder="State...(e.g. VIC)" name="state"><br><br>
                    Postcode <br><input class="input-voter" type="text" placeholder="Postcode...(e.g. 3000)" name="postcode"><br><br>
                    Have you voted before in THIS election? (Tick if already voted)
                    <br><input type="checkbox" style="width:auto" name="voted"><br><br> 
                    <button type="submit" name="submit" value="submit">Next</button>
                </form>
                
            </div>
            
        </body>
    </main>

</html>