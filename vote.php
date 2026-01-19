<html>
    <link rel="stylesheet" type="text/css" href="style.css">
    <main>
        <body>
            <?php 
                // establish a database connection to your Oracle database.
                $username = 'S3816935';
                $password = 'orABGD@2023!';
                $servername = 'talsprddb01.int.its.rmit.edu.au';
                $servicename = 'CSAMPR1.ITS.RMIT.EDU.AU';
                $connection = $servername."/".$servicename;

                $conn = oci_connect($username, $password, $connection);
                if(!$conn) 
                {
                    $e = oci_error();
                    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
                }
                else 
                {
                    // Display all user inputs
                    // echo 'User full name: ' . $_POST["full_name"] . '<BR>';
                    // echo 'User address: ' . $_POST["address"] . '<BR>';
                    // echo 'User apartment: ' . $_POST["appartment"] . '<BR>';
                    // echo 'User suburb: ' . $_POST["suburb"] . '<BR>';
                    // echo 'User state: ' . $_POST["state"] . '<BR>';
                    // echo 'postcode: ' . $_POST["postcode"] . '<BR>';
                    // echo 'voted before: ' . $_POST["voted"] . '<BR>';

                    // $queryVoter = 'SELECT * FROM Voters';
                    
                    

                    // If already voted ----------------------------------------------------------------------------------------------------
                    if(isset($_POST["voted"])) 
                    {
                        echo '<script language="javascript">';
                        echo 'alert("You have already voted in this election, you cannot vote again! ") ';
                        echo '</script>';
                        // header('Location: ' . $_SERVER['HTTP_REFERER']);
                    }
                    // Claims not voted -----------------------------------------------------------------------------------------------------
                    else 
                    {
                        // Check if voter exists ------------------------------------------------------------------------------------------------
                        $queryVoter = "SELECT * FROM Voters 
                        WHERE :name_bv LIKE LOWER (Voters.FirstName || ' ' || Voters.LastName)"; 
            
                        $voterExists = oci_parse($conn, $queryVoter);

                        $name = $_POST["full_name"];
                        $name = strtolower($name);
                        echo $name;

                        oci_bind_by_name($voterExists, ":name_bv", $name);
                        
                        oci_execute($voterExists);

                        $voter = oci_fetch_array($voterExists, OCI_ASSOC+OCI_RETURN_NULLS);
                        print_r($voter);
                        // Get voter ID
                        $voterID = $voter["VOTERID"]; 
                        echo $voterID;
                        
                        $voter = oci_num_rows($voterExists);

                        // echo "<table border='1'>\n";
                        // echo "<tr>";
                        // while ($row = oci_fetch_array($voterExists, OCI_ASSOC+OCI_RETURN_NULLS)) {
                        //     echo "<tr>\n";
                        //     foreach ($row as $item) {
                        //         echo "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
                        //     }
                        //     echo "</tr>\n";
                        // }
                        // echo "</table>\n";

                        echo $voter;
                        if($voter === 0)
                        {
                            echo 'Your name or address does not match'; 
                            // Ask user to re-enter their info
                        }
                        else
                        {
                            echo 'Valid voter'; 

                            // Check if voter voted before ----------------------------------------------------------------------------------------
                            $queryVotedBefore = "SELECT * FROM IssuanceRec
                                        WHERE ElectionCode = '20220521'
                                        AND :id_bv = IssuanceRec.VoterID";

                            $votedBefore = oci_parse($conn, $queryVotedBefore);

                            oci_bind_by_name($votedBefore, ":id_bv", $voterID);

                            oci_execute($votedBefore); 

                            
                            $voted = oci_fetch_array($votedBefore, OCI_ASSOC+OCI_RETURN_NULLS); 
                            $num = oci_num_rows($votedBefore);
                            if($num > 0)
                            {
                                echo 'You already voted'; 
                            }
                            else
                            {
                                echo 'Allowed to vote'; 
                            }
                        }
                    }
                    oci_close($conn);
                }
            ?>
        </body>
    </main>

</html>