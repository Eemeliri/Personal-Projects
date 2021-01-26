var http = require("http");
var fs = require("fs");

http.createServer(function (request, response) {
    // Luetaan html filu ja palautetaan sen sisältö client:lle
    // HUOM! grid.htm täytyy olla samassa hakemistossa kuin tämä index.js
    
    fs.readFile("grid.htm", function (err, data) {
        response.writeHead(300, { 'Content-type': 'text/html' });
        response.write(data);
        response.end();
    });

    // Alla helpoin mahdollinen esimerkki
    //response.write("Hello
");

    // Alla "täydellistä html:ää"
	
    
    /**response.write("<!doctype>")
    response.write("<html><head><title>Eka sivu</title><head>");
    response.write("<body><p>Terve maailma</p></body></html>");
    response.end();
}).listen(3002);**/

// Kutsu serveriä selaimesta osoitteella http://localhost:3002
console.log("Server running at http://localhost:3002");