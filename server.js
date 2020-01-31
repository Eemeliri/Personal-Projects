
// Asenna ensin express npm install express --save

var express = require('express');
var app=express();

var bodyParser = require('body-parser');
var customerController = require('./customerController');

const http = require('http');
const url = require('url');

const hostname = '127.0.0.1';
const port = process.env.PORT || 3001;

var express = require('express');
var app = express();
var bodyParser = require('body-parser');
var urlencodedParser = bodyParser.urlencoded({ extended: true });


//CORS middleware
var allowCrossDomain = function(req, res, next) {
    res.header('Access-Control-Allow-Origin', '*');
 //   res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
 //   res.header('Access-Control-Allow-Headers', 'Content-Type');

    next();
}

app.use(allowCrossDomain);

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.get('/form', function (req, res) {
  var html='';
  html +="<body>";
  html += "<form action='/thank'  method='post' name='form1'>";
  html += "Avain:</p><input type= 'text' name='avain'>";
  html += "Nimi:</p><input type='text' name='nimi'>";
  html += "Osoite:</p><input type='text' name='osoite'>";
  html += "Postinro:</p><input type='text' name='postinro'>";
  html += "PostiTMP:</p><input type='text' name='postitmp'>";
  html += "Luontipvm:</p><input type='text' name='pvm'>";
  html += "Asty Avain:</p><input type='text' name='astyavain'>";
  html += "<input type='submit' value='submit'>";
  html += "<INPUT type='reset'  value='reset'>";
  html += "</form>";
  html += "</body>";
  res.send(html);
});
 
app.post('/thank', urlencodedParser, function (req, res){
  var reply='';

  .post(customerController.create);
 });

// Staattiset filut
app.use(express.static('public'));

// REST API Asiakas
app.route('/Types')
    .get(customerController.fetchTypes);


app.route('/Asiakas')
    .get(customerController.fetchAll)
    .post(customerController.create);
	
app.route('/Poista/:id')
	.post(customerController.delete);

app.route('/Kaikki')
    .get(customerController.fetchAsiakas);
	
//
app.route('/Kaikki/:id')
	.get(customerController.fetchNimi)
	.get(customerController.fetchOsoite)
	.get(customerController.fetchTyyppi);
	
app.route('/Tyypit')
	.get(customerController.fetchAsiakasTyypit);


app.get('/', function(request, response){
    response.statusCode = 200;
    response.setHeader('Content-Type', 'text/plain');
    response.end("Terve maailma"); 
});

app.get('/maali', function(request, response){
    response.statusCode = 200;
    response.setHeader('Content-Type', 'text/plain');
    response.end("Maalit 2-3"); 
});

app.route('/task')
    .get(function(request, response){
        response.statusCode = 200;
        response.setHeader('Content-Type', 'text/plain');
        response.end("Taskeja pukkaa");     
    });

app.listen(port, hostname, () => {
  console.log(`Server running AT http://${hostname}:${port}/`);
});

/*
app.listen(port, () => {
    console.log(`Server running AT http://${port}/`);
  });
*/  