/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
match();

//UserID = logged in user's id
function match(userID) {
    var mysql = require('mysql');
    var firstMatchesList = [];
    //list with objects formatted as {id : users id, percent : percent of match}
    var finalMatchesList = [];

    var con = mysql.createConnection({
        host: "localhost",
        user: "root",
        password: "root",
        database: "ginder"
    });

    con.connect(function (err) {
        if (err) throw err;

        var userResults;
        var friendsResults;
        //Select all from table userdata where id = logged in users id
        var sqlUser = 'SELECT * FROM userdata WHERE id = ?';
        con.query(sqlUser, [userID], function (err, result) {
            if (err) throw err;
            //save results in userResult
            userResults = result;

            //Select all from table userdata
            var sqlFriends = 'SELECT * FROM userdata';
            con.query(sqlFriends, function (err, result) {
                if (err) throw err;
                //save results in friendResults
                friendsResults = result;
                
                //Array to store values from every field
                var userValues = [];
                
                //Get user values by field name
                userValues.push(userResults[0].meet_male);
                userValues.push(userResults[0].meet_female);
                userValues.push(userResults[0].meet_youth);
                userValues.push(userResults[0].meet_adults);
                userValues.push(userResults[0].meet_elderly);
                userValues.push(userResults[0].morning);
                userValues.push(userResults[0].noon);
                userValues.push(userResults[0].afternoon);
                userValues.push(userResults[0].evening);
                userValues.push(userResults[0].location1);
                userValues.push(userResults[0].location2);
                userValues.push(userResults[0].location2);
                userValues.push(userResults[0].location3);
                userValues.push(userResults[0].duration);
                userValues.push(userResults[0].restriction);
                userValues.push(userResults[0].third_party);
                //console.log(userValues);

                //Loop through every users' data fields
                for (var i = 0; i < friendsResults.length; i++) {
                    
                    //Array to store values to
                    var friendValues = [];
                    friendValues.push(friendsResults[i].meet_male);
                    friendValues.push(friendsResults[i].meet_female);
                    friendValues.push(friendsResults[i].meet_youth);
                    friendValues.push(friendsResults[i].meet_adults);
                    friendValues.push(friendsResults[i].meet_elderly);
                    friendValues.push(friendsResults[i].morning);
                    friendValues.push(friendsResults[i].noon);
                    friendValues.push(friendsResults[i].afternoon);
                    friendValues.push(friendsResults[i].evening);
                    friendValues.push(friendsResults[i].location1);
                    friendValues.push(friendsResults[i].location2);
                    friendValues.push(friendsResults[i].location2);
                    friendValues.push(friendsResults[i].location3);
                    friendValues.push(friendsResults[i].duration);
                    friendValues.push(friendsResults[i].restriction);
                    friendValues.push(friendsResults[i].third_party);
                    //console.log(friendValues);
                    
                    //Array to store all of the same values
                    var similiar = [];
                    
                    //Compare every value on userValues and friendValues
                    //If the values are the same (e.g. 1 === 1), store this value to array
                    for (var value = 0; value < friendValues.length; value++) {
                        if (friendValues[value] === userValues[value]) {
                            similiar.push(friendValues[value]);
                        }
                    }
                    
                    //Calculate percent of match
                    //Length of similiar values divided by length of userValues
                    var percent = Math.round((similiar.length / userValues.length) * 100);
                    
                    console.log("Match per cent between users " , userResults[0].id , " and ", friendsResults[i].id , " is: " , percent);
                    
                    //If match per cent is greater than 50, save friend for later use
                    if (percent >= 50) {
                        firstMatchesList.push(friendsResults[i]);
                    }
                }
            });
        });
        //console.log(firstMatchesList);
    });
    /*
     con.connect(function(err) {
     if (err)throw err;
     //Add here method for getting logged in users ID
     var userID = '';
     var userInterestsResults;
     //select all interests_id's from table user_interests where userdata_id = leged i users id
     var sqlUserInterests = 'SELECT interests_id FROM user_interests WHERE userdata_id = ?';
     con.query(sqlUserInterests, [userID], function (err, result) {
     if (err) throw err;
     //save results in userInterestsResults
     userInterestsResults = result;
     
     var userInterestsList = [];
     var row1;
     for (row1 in userInterestsResults){
     //save only interests id
     userInterestsList.push(row1.interests_id);
     }
     
     var friend;
     for (friend in firstMatchesList) {
     var friendID = friend;
     var friendInterestsResults;
     //select all from table user_interests where userdata_id = friend id
     var sqlFriendInterests = 'SELECT * FROM user_interests WHERE userdata_id = ?';
     con.query(sqlFriendInterests, [friendID], function (err, result) {
     if (err) throw err;
     //save results
     friendInterestsResults = result;
     var friendInterestsList = [];
     var row2;
     for (row2 in friendInterestsResults){
     friendInterestsList.push(row2.interests_id);
     }
     
     var similiar1 = _.intersection ( userInterestsList, friendInterestsList);
     var percent1 = Math.round(similiar1.length * (100 / userInterestsList.length));
     if (percent >= 50) {
     finalMatchesList.push({ id : friendID, Percent : percent1});
     }
     
     });    
     
     }
     });
     });*/
}