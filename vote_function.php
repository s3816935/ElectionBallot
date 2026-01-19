<?php 
$username = 'S3816935';
$password = '2023';
$servername = 'talsprddb01.int.its.rmit.edu.au';
$servicename = 'CSAMPR1.ITS.RMIT.EDU.AU';
$connection = $servername."/".$servicename;

$conn = oci_connect($username, $password, $connection);
if(!$conn) 
{
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

function queryConnection($query) {
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
    
    $stid = oci_parse($conn, $query);
    return $stid;
    
}

function validateVoter($form) {
    $error = []; 
    // Check if voter exists ---------------------------------------------------------------------------------------------------
    $name = $form["full_name"]; 
    $voterInfo = findVoter($name); 
    if($voterInfo === null) {
        $error[0] = "Your name or address does not match, either you're not a registered voter or 
                    your entered information is incorrect. Please re-enter if you are a registered voter";
        return $error;
    }
    // return $voterInfo[1];
    $voterID = $voterInfo[0]; 
    $voterElectorate = $voterInfo[1];

    // Check if voter already voted ---------------------------------------------------------------------------------------------
    $num = votedBefore($voterID); 
    if($num > 0) {
        $error[0] = "You have already voted, you cannot vote again! Please note that voter fraud is a criminal offence! ";
        return $error;
    }
    $error[0] = 0; 
    $error[1] = $voterID;
    $error[2] = $voterElectorate;
    return $error;

}

function findVoter($name) {
    $query = "SELECT * FROM Voters 
            WHERE :name_bv LIKE LOWER (Voters.FirstName || ' ' || Voters.LastName)"; 

    $name = strtolower($name);
    $stid = queryConnection($query);
    oci_bind_by_name($stid, ":name_bv", $name);
                        
    oci_execute($stid);

    $voter = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);    // Fetch array
    $row = oci_num_rows($stid);    // Row would be 0 if nothing fetched (voter not found)
    if($row === 0) {
        return null;
    }
    else {    // If voter found, return voterID and voter's electorate 
        $voterInfo = []; 
        $voterInfo[0] = $voter["VOTERID"];
        $voterInfo[1] = $voter["ELECTORATE"]; 
        return $voterInfo;
    }
}

function votedBefore($voterID) {
    $query = "SELECT * FROM IssuanceRec
            WHERE ElectionCode = '20220521'
            AND :id_bv = IssuanceRec.VoterID";
    
    $stid = queryConnection($query);
    oci_bind_by_name($stid, ":id_bv", $voterID);

    oci_execute($stid);
    $voted = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);    // See if voter is in the IssuanceRec of 2022 election
    $row = oci_num_rows($stid);
    
    return $row;

}

function findCandidates($electorate) {
    // Find Candidates from the voter's electorate -----------------------------------------------------------------------------
    $query = "SELECT Candidates.CandidateID AS CandidateID, Candidates.Name AS Candidate, Parties.Name AS Party 
            FROM Candidates INNER JOIN Parties ON Candidates.PoliticalPartyCode = Parties.PartyCode
            WHERE Candidates.Electorate = :electorate_bv";
    
    $stid = queryConnection($query);
    oci_bind_by_name($stid, ":electorate_bv", $electorate);

    oci_execute($stid);
    oci_fetch_all($stid, $candidates);

    return $candidates;
}

function processVote($voterID, $electorate, $candidateIDs, $preferences) {
    
    // BallotID, ElectionCode, Electorate 
    $queryBallot = "INSERT INTO Ballots 
                VALUES (ballot_seq.NEXTVAL, '20220521', :electorate_bv)
                RETURNING BallotID INTO :insertedID";

    // ElectionCode, VoterID, Electorate
    $queryIssuanceRec = "INSERT INTO IssuanceRec 
                VALUES ('20220521', :vid_bv, :electorate_bv)"; 

    // BallotID, CandidateID, Preferences[]
    $queryPreference = "INSERT INTO Preferences 
                VALUES (:bid_bv, :candidateID, :preference)"; 

    // Ballot query -------------------------------------------------------------------------------------------------------
    $stidBallot = queryConnection($queryBallot); 
    oci_bind_by_name($stidBallot, ":electorate_bv", $electorate);
    oci_bind_by_name($stidBallot, ":insertedID", $insertedID, -1, OCI_B_INT);
    oci_execute($stidBallot);

    // Define the result variable for insertedID
    oci_define_by_name($stidBallot, 'BALLOT_ID', $insertedID);

    // Issuance query -------------------------------------------------------------------------------------------------------
    $stidIssuance = queryConnection($queryIssuanceRec);
    oci_bind_by_name($stidIssuance, ":vid_bv", $voterID);
    oci_bind_by_name($stidIssuance, ":electorate_bv", $electorate);
    oci_execute($stidIssuance);

    // Preference query -------------------------------------------------------------------------------------------------------
    $stidPreference = queryConnection($queryPreference);
    for ($i = 0; $i < count($candidateIDs); $i++) {
        oci_bind_by_name($stidPreference, ":bid_bv", $insertedID);
        oci_bind_by_name($stidPreference, ":candidateID", $candidateIDs[$i]);
        oci_bind_by_name($stidPreference, ":preference", $preferences[$i]);
        oci_execute($stidPreference);
    }

    $message = "Your vote has been submitted. Thanks for voting! ";
    return $message; 

}

?>
