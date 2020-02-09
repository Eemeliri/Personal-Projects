<?php

/* 
 * Match users
 * Needs user id as parameter.
 * Returns $finalMatchesList that contains
 * friendID => percent pairs that have at least 10% match.
 * 
 */


function match($id) {

    //Get logged in user's data rows
    $user = getUser($id);
    $userdata = getUserdata($id);
    
    //Get filtering values like location, age group and gender preferences
    //These values are used to filter out unwanted results
    $searchCriteria = getSearchCriteria($user, $userdata);
    
    //Array of values from userdata table
    $userValues = getUserValues($userdata);
    
    //Get all user id's
    //Use filters from $searchCriteria
    $firstMatchesList = search($id, $searchCriteria, $userValues);
    
    //Get user interest values
    $userInterestValues = getInterests($id);
    
    $finalMatchesList = [];
    
    //Get friend interest values and compare with user interest values
    foreach ($firstMatchesList as $friend) {
        $sameInterests = [];
        
        //Current friend id
        $friendID = key($friend);

        //Get friends interest values
        $friendInterestValues = getInterests($friendID);
        //Compare interest values between user and friend
        foreach ($friendInterestValues as $value) {
            if (in_array($value, $userInterestValues)) {
                array_push($sameInterests, $value);
            }
        }

        //Initial match percent
        //Weight is 2/1
        $friendMatch = current($friend);
        $percent = round((count($sameInterests) / count($userInterestValues) * 100), 1);
        $finalPercent = round((($friendMatch * 2 + $percent) / 3), 1); 
        //Minimum of accepted persent is 10%
        if($finalPercent >= 10) {
            $keyvalue = array($friendID => $finalPercent);
            array_push($finalMatchesList, $keyvalue);
        }
    }
    
    //Sort by match %
    usort($finalMatchesList, function($a, $b) {return current($b) <=> current($a);});
    
    return $finalMatchesList;
}

//Get logged in user's values from users table
function getUser($id) {
    global $host, $db_name, $username, $pass;
    
    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");

    //Prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

//Get logged in user's values from userdata table
function getUserdata($id) {
    global $host, $db_name, $username, $pass;
    
    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");

    //Prepared statement
    $stmt = $conn->prepare("SELECT * FROM userdata WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

//Collects certain fields from user/userdata tables, like location, usertype and meeting groups
//These values are used to filter out unwanted results
function getSearchCriteria($user, $userdata) {
    
    $filters = array(
        'type' => $user['type'],
        'locality' => $user['locality'],
        'meet_male' => $userdata['meet_male'],
        'meet_female' => $userdata['meet_female'],
        'meet_all' => $userdata['meet_all'],
        'meet_youth' => $userdata['meet_youth'],
        'meet_adults' => $userdata['meet_adults'],
        'meet_elderly' => $userdata['meet_elderly']
    );
    
    //var_dump($filters);
    return $filters;
}

//Get matches for user
//First, filter users by gender
//Then compare values with selected users
function search($id, $searchCriteria, $userValues) {
    global $host, $db_name, $username, $pass;
    
    //Get user ids with gender value specified in $searchCriteria
    $ids1 = searchByGender($id, $searchCriteria);
    //Get user ids with age value specified in $searchCriteria
    $ids2 = searchByAge($id, $searchCriteria);
    //Remove all ids that are not in $ids1
    //Array of all ids that fulfill specifications fro gender and age
    $ids3 = array_intersect($ids1, $ids2);
    //Check if $ids2 or $ids3 are null
    if ($ids2 == null && $ids3 == null){
        $ids = $ids1;
    }
    else {
        $ids = $ids3;
    }
    
    //CHeck if users are already matched
    $checkedMatched = CheckIfMatched($id, $ids);
    //Check user type
    $checkedTypes = CheckType($id, $checkedMatched, $searchCriteria);
            
    //Array to store matches
    $firstMatchesList = [];
    
    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");

    //Loop through every user id found by gender and age filtering
    foreach($checkedTypes as $friendID) {
        $stmt = $conn->prepare("SELECT * FROM userdata WHERE id = ?");
        $stmt->bind_param('i', $friendID);
        $stmt->execute();
        $friendResult = $stmt->get_result();
        $friendRow = $friendResult->fetch_assoc();
        
        //Store values in array to comparison
        $friendValues = [];
        array_push($friendValues, $friendRow['morning']);
        array_push($friendValues, $friendRow['noon']);
        array_push($friendValues, $friendRow['afternoon']);
        array_push($friendValues, $friendRow['evening']);
        array_push($friendValues, $friendRow['location2']);
        array_push($friendValues, $friendRow['location1']);
        array_push($friendValues, $friendRow['location3']);
        array_push($friendValues, $friendRow['duration']);
        array_push($friendValues, $friendRow['restriction']);
        array_push($friendValues, $friendRow['third_party']);

        //Array to store similiar values
        $similiar = [];

        //Compare user values and friend values
        for ($i = 0; $i < count($userValues); $i++) {
            if ($userValues[$i] == $friendValues[$i]) {
                array_push($similiar, $friendValues[$i]);
            }
        }

        //Percent of match, calculated by array length of same values
        $percent = round((count($similiar) / count($userValues)) * 100, 1);

        //Push friend id and match percent in $firstMatchesList
        //if match percent is greater than 20,
        if ($percent >= 20) {
            $keyvalue = array($friendRow['id'] => $percent);
            array_push($firstMatchesList, $keyvalue);
        }
        $stmt->close();
    }

    
    
    return $firstMatchesList;
}


//Get user ids filtered by gender preferences
function searchByGender($id, $searchCriteria) {
    global $host, $db_name, $username, $pass;

    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");
    
    //Query result
    $result = null;
    $friends = [];

    //Meet male and female
    //meet_all not selected
    if (!empty($searchCriteria['meet_male']) && !empty($searchCriteria['meet_female']) && empty($searchCriteria['meet_all'])) {    
        $gender1 = 'male';
        $gender2 = 'female';
        $stmt = $conn->prepare("SELECT id FROM users WHERE gender = ? OR gender = ? AND id != ?");
        $stmt->bind_param('ssi', $gender1, $gender2, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($friends, $row['id']);
        }
        $stmt->close(); 
    }
    
    //Meet all
    //anything else can be selected
    if (!empty($searchCriteria['meet_all'])) {     
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($friends, $row['id']);
        }
        $stmt->close();
    }

    //Meet male only
    if (!empty($searchCriteria['meet_male']) && empty($searchCriteria['meet_female']) && empty($searchCriteria['meet_all'])) {
        $gender = 'male';
        $stmt = $conn->prepare("SELECT id FROM users WHERE gender = ? AND id != ?");
        $stmt->bind_param('si', $gender, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($friends, $row['id']);
        }
        $stmt->close();
    } 

    //Meet female only
    if (empty($searchCriteria['meet_male']) && !empty($searchCriteria['meet_female']) && empty($searchCriteria['meet_all'])) {
        $gender = 'female';
        $stmt = $conn->prepare("SELECT id FROM users WHERE gender = ? AND id != ?");
        $stmt->bind_param('si', $gender, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($friends, $row['id']);
        }
        $stmt->close();
    } 
    
    return $friends;
}


//Get user ids filtered by age preference
function searchByAge($id, $searchCriteria) {
    global $host, $db_name, $username, $pass;
    
    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");
    
    //Query results
    $result = null;
    $newFriends = [];
    
    //Information needed fro age specification
    $current_date = Date("Y");
    //Gives the last year person is considered youth
    $youth_stop = (string)($current_date - 32).'-01-01';
    //Gives the last year person is considered as adult
    $adult_stop = (string)($current_date - 54).'-01-01';
    //Gives the first year person is considered as elderly
    $elderly_start = (string)($current_date - 55).'-01-01';
    
    //Meet all
    if (!empty($searchCriteria['meet_elderly']) && !empty($searchCriteria['meet_adults']) && !empty($searchCriteria['meet_youth'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ? ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($newFriends, $row['id']);
        }
        $stmt->close();
    }
    
    //Only youth selected
    if (empty($searchCriteria['meet_elderly']) && empty($searchCriteria['meet_adults']) && !empty($searchCriteria['meet_youth'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ? AND date_of_birth >= ?");
        $stmt->bind_param('is', $id, $youth_stop);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($newFriends, $row['id']);
        }
        $stmt->close();
    }
    
    //Only adults selected
    if (empty($searchCriteria['meet_elderly']) && !empty($searchCriteria['meet_adults']) && empty($searchCriteria['meet_youth'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ? AND date_of_birth BETWEEN ? AND ? ");
        $stmt->bind_param('iss', $id, $youth_stop, $adult_stop);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($newFriends, $row['id']);
        }
        $stmt->close();
    } 
    
    //Only elderly selected
    if (!empty($searchCriteria['meet_elderly']) && empty($searchCriteria['meet_adults']) && empty($searchCriteria['meet_youth'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ? AND date_of_birth <= ? ");
        $stmt->bind_param('is', $id, $elderly_start);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($newFriends, $row['id']);
        }
        $stmt->close();
    }
    
    //Youth and adults selected
    if (empty($searchCriteria['meet_elderly']) && !empty($searchCriteria['meet_adults']) && !empty($searchCriteria['meet_youth'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ? AND date_of_birth >= ? ");
        $stmt->bind_param('is', $id, $adult_stop);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($newFriends, $row['id']);
        }
        $stmt->close();
    } 
    
    //Adults and elderly selected
    if (!empty($searchCriteria['meet_elderly']) && !empty($searchCriteria['meet_adults']) && empty($searchCriteria['meet_youth'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ? AND date_of_birth < ? ");
        $stmt->bind_param('is', $id, $youth_stop);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($newFriends, $row['id']);
        }
        $stmt->close();
    } 
    
    //Elderly and youth selected
    if (!empty($searchCriteria['meet_elderly']) && empty($searchCriteria['meet_adults']) && !empty($searchCriteria['meet_youth'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id != ? AND date_of_birth >= ? AND date_of_birth <= ?");
        $stmt->bind_param('iss', $id, $youth_stop, $elderly_start);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($newFriends, $row['id']);
        }
        $stmt->close();
    } 

    return $newFriends;
}

//Filters ids that are already matched
function CheckIfMatched($id, $ids) {
    global $host, $db_name, $username, $pass;
    
    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");
    
    $unChecked = [];
    
    //Prepared statement
    $stmt = $conn->prepare("SELECT sender_id, receiver_id FROM user_matches WHERE sender_id = ? OR receiver_id = ?");
    $stmt->bind_param('ii', $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['sender_id'] == $id ){
            array_push($unChecked, $row['receiver_id']);
        }
        if ($row['receiver_id'] == $id){
            array_push($unChecked, $row['sender_id']);
        }
    }
    $Checked = [];

    
    foreach ($ids as $value){
        if (!(in_array($value, $unChecked))){
            array_push($Checked, $value);
        }
    }
    
    $stmt->close();
    
    return $Checked;
}

//Filters out not allowed user types
function CheckType($id, $ids, $searchCriteria) {
    
    global $host, $db_name, $username, $pass;
    
    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");
    
    $unChecked = [];
    
    //User types
    $type = $searchCriteria['type'];
    $normal_user = 'basic';
    $volunteer = 'volunteer';
    
    //Prepared statement
    //Filters types based on users type $type
    $stmt = $conn->prepare("SELECT id, type FROM users WHERE id != ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($type == $volunteer && $row['type'] == $normal_user) {
            array_push($unChecked, $row['id']);
        }
        if ($type == $normal_user && ($row['type'] == $normal_user || $row['type'] == $volunteer)){
            array_push($unChecked, $row['id']);
        }
    }
    $Checked = [];
    
    //Check if ids in array $ids are in array of ids with accepted type
    foreach ($ids as $value){
        if (in_array($value, $unChecked)){
            array_push($Checked, $value);
        }
    }
    
    $stmt->close();
    
    return $Checked;  
}


//Get user values from table userdata
function getUserValues($userdata) {
    $uservalues = [];
    
    array_push($uservalues, $userdata['morning']);
    array_push($uservalues, $userdata['noon']);
    array_push($uservalues, $userdata['afternoon']);
    array_push($uservalues, $userdata['evening']);
    array_push($uservalues, $userdata['location1']);
    array_push($uservalues, $userdata['location2']);
    array_push($uservalues, $userdata['location3']);
    array_push($uservalues, $userdata['duration']);
    array_push($uservalues, $userdata['restriction']);
    array_push($uservalues, $userdata['third_party']);
    
    return $uservalues;
}

//Get user's interests
function getInterests($id) {
    global $host, $db_name, $username, $pass;
    
    //Connect to database
    $conn = new mysqli($host, $username, $pass, $db_name);
    mysqli_set_charset($conn, "utf8");
    
    //Prepared statement
    $stmt = $conn->prepare("SELECT interest_id FROM user_interests WHERE user_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $InterestValues = [];
    
    while($row = $result->fetch_assoc()) {
        array_push($InterestValues, $row['interest_id']);
    }
    $stmt->close();
    
    return $InterestValues;
}

