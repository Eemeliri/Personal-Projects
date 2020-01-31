'use strict'

// Asenna ensin mysql driver 
// npm install mysql --save

var mysql = require('mysql');

var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',  // HUOM! Älä käytä root:n tunnusta tuotantokoneella!!!!
  password : '',
  database : 'Customer'
});



module.exports = 
{
    fetchTypes: function (req, res) {  
      connection.query('SELECT Avain, Lyhenne, Selite FROM Asiakastyyppi', function(error, results, fields){
        if ( error ){
          console.log("Virhe haettaessa dataa Asiakas-taulusta, syy: " + error);
          //res.send(error);
          res.send(JSON.stringify({"status": 500, "error": error, "response": null})); 
        }
        else
        {
          console.log("Data = " + JSON.stringify(results));
          //res.json(results);
          res.statusCode = 200;
          //res.send(results);
          res.send({ "status": 768, "error": null, "response": results });
        }
    });

    },
	
	fetchAsiakas: function(req, res) {
		connection.query('SELECT Asiakas.NIMI FROM Asiakas', function(error, results, fields) {
			      if ( error ){
          console.log("Virhe haettaessa dataa Asiakas-taulusta, syy: " + error);
          //res.send(error);
          res.send(JSON.stringify({"status": 500, "error": error, "response": null})); 
        }
        else
        {
          console.log("Data = " + JSON.stringify(results));
          //res.json(results);
          res.statusCode = 200;
          //res.send(results);
          res.send({ "status": 768, "error": null, "response": results });
        }
    });
	},
	
	fetchNimi: function(req, res) {
		connection.query('SELECT Asiakas.NIMI from Asiakas WHERE Asiakas.AVAIN LIKE "%id"', function(error, results, fields) {
			   if ( error ){
          console.log("Virhe haettaessa dataa Asiakas-taulusta, syy: " + error);
          //res.send(error);
          res.send(JSON.stringify({"status": 500, "error": error, "response": null})); 
        }
        else
        {
          console.log("Data = " + JSON.stringify(results));
          //res.json(results);
          res.statusCode = 200;
          //res.send(results);
          res.send({ "status": 768, "error": null, "response": results });
        }
    });
	},
	
	fetchOsoite: function(req, res) {
		connection.query('SELECT Asiakas.OSOITE from Asiakas WHERE Asiakas.AVAIN LIKE "%id"', function(error, results, fields) {
			   if ( error ){
          console.log("Virhe haettaessa dataa Asiakas-taulusta, syy: " + error);
          //res.send(error);
          res.send(JSON.stringify({"status": 500, "error": error, "response": null})); 
        }
        else
        {
          console.log("Data = " + JSON.stringify(results));
          //res.json(results);
          res.statusCode = 200;
          //res.send(results);
          res.send({ "status": 768, "error": null, "response": results });
        }
    });
	},
	
	fetchTyyppi: function(req, res) {
		connection.query('SELECT selite from Asiakastyyppi WHERE Asiakas.ASTY_AVAIN=1 WHERE "%id"', function(error, results, fields) {
			   if ( error ){
          console.log("Virhe haettaessa dataa Asiakas-taulusta, syy: " + error);
          //res.send(error);
          res.send(JSON.stringify({"status": 500, "error": error, "response": null})); 
        }
        else
        {
          console.log("Data = " + JSON.stringify(results));
          //res.json(results);
          res.statusCode = 200;
          //res.send(results);
          res.send({ "status": 768, "error": null, "response": results });
        }
    });
	},
	fetchAsiakasTyypit: function(req, res) {
		connection.query('SELECT Asiakastyyppi.selite from Asiakastyyppi"', function(error, results, fields) {
			   if ( error ){
          console.log("Virhe haettaessa dataa Asiakas-taulusta, syy: " + error);
          //res.send(error);
          res.send(JSON.stringify({"status": 500, "error": error, "response": null})); 
        }
        else
        {
          console.log("Data = " + JSON.stringify(results));
          //res.json(results);
          res.statusCode = 200;
          //res.send(results);
          res.send({ "status": 768, "error": null, "response": results });
        }
    });
	},

    fetchAll: function(req, res){
        console.log("Body = " + JSON.stringify(req.body));
        console.log("Params = " + JSON.stringify(req.query));
        res.send("Kutsuttiin fetchAll");
    },

    create: function(req, res){
        // Client lähettää POST-moethod:n
        console.log("Data = " + JSON.stringify(req.body));
        res.send("Kutsuttiin create");
    },

    update: function(req, res){

    },

    delete : function (req, res) {
        // Client lähettää DELETE method:n
        console.log("Body = " + JSON.stringify(req.body));
        console.log("Params = " + JSON.stringify(req.params));
		var sql = "DELETE FROM Asiakas WHERE Asiakas.avain = $id";
        //res.send("Kutsuttiin delete");
    }
}
