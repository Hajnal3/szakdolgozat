<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>HU monitor</title>
<style>
* {
	box-sizing: border-box;
}
body {
	--background: url('harman_elforgatott.png') no-repeat;
	background-color: white;
	--background-color: #f2f2f2;
	font-family: 'Segoe Ui';
	padding: 0;
	margin: 0;
	FONT-SIZE: 2EM;
	--FONT-SIZE: 1.35EM;
}
#container{
	padding: 0;
	margin: 0;
}


#alsocsik{
	position: fixed;
	bottom: 0;
}


#tablazat {
	margin-right: 0.5em;
	text-align: right;
}


#tablazat table {
	display: inline-table;
	border-collapse: collapse;
	text-align: left;
	width: 1250px;
}

#tablazat td{
	padding: 2px 10px;
}

#tablazat table tr:nth-child(even) {background: #CCC}
#tablazat table tr:nth-child(odd) {background: #FFF}

#tablazat table tr th{
	background: lightgrey;
}

#tablazat table tr.zold:nth-child(even){
	background: rgb(146,208,80);
}

#tablazat table tr.zold:nth-child(odd), #legend div.zold{
	background: rgb(0,176,80);
}

#tablazat table tr.sarga:nth-child(even), #legend div.sarga{
	background: rgb(255,192,0);
}

#tablazat table tr.sarga:nth-child(odd){
	background: rgb(255,255,0);
}

#tablazat table tr.piros:nth-child(even), #legend div.piros{
	background: rgb(255,0,0);
	color: yellow;
}

#tablazat table tr.piros:nth-child(odd){
	background: rgb(196,0,0);
	color: yellow;
}

th.carrier, td.carrier {
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

th.transcustomer, td.transcustomer {
    max-width: 200px;
    aoverflow: hidden;
    atext-overflow: ellipsis;
    awhite-space: nowrap;
}

td.idokapu {
	width: 190px;
}

td.category{
	width: 100px;
}

td.platenumber{
	width: 100px;
}

#updated{
	background: #f2f2f2;
	border: 3px solid white;
	padding: 2px;
	font-size: 0.5em;
}

#legend{
	-display: none;
	font-size: 0.5em;
	position: fixed;
	top: 150px;
	left: 1em;
	width: 210px;
}

#legend div{
	padding: 0 10px;
}

#harman_logo{	
	-background-image: url('harman_logo.png');
	background-repeat: no-repeat;
	background-position: center;
	background-size: cover;
	position: fixed;
	top:0px;
	left:0px;
	z-index:-1;
}
	

#datumido {
    position: fixed;
    bottom: 10px;
    left: 10px;
}

#ido{
    font-size: 7em;
    text-align: center;
}
#datum{
    font-size: 2em;
    text-align: center;
}
</style>
</head>
<body>
<div id="container" style="">
	<div id="tablazat"></div>
	<span id="harman_logo"><img src="harman_logo.png" /></span>
	<div id="legend">
		<h2>Sürgős HU monitor</h2>
		<p>
			<b>Időkapu:</b>
			<div class="piros">Lejárt</div>
			<div class="sarga">1 órán belüli</div>
			<div class="zold">1 órán túli</div>
		</p>
	</div>
    <div id="datumido">
        Idő:
        <div id="ido">###</div>
        <div id="datum">###</div>
    </div>
</div>
<!--
<div id="alsocsik" style="width: 100%">
	<span id="updated" style="float: left">#</span>
</div>
-->
<script>
var data;
window.onload = function() {
	setup_updater();
        
    setInterval(update_seconds, 1000);
}

function padZero(be) {
    if(be<10) return "0" + be;
    return be;
}


function update_seconds() {
    
    var ido = document.getElementById("ido");
    var datum = document.getElementById("datum");
    
    var most = new Date();
    ido.innerHTML = padZero(most.getHours()) + ":" + padZero(most.getMinutes());
    
    datum.innerHTML = most.getFullYear() + "." + padZero(most.getMonth() + 1) + "." + padZero(most.getDate());
}

function setup_updater() {
	var update_minutes = 15;

	update_data();
	setInterval(update_data, update_minutes * 60 * 1000);
}

function update_data() {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState == XMLHttpRequest.DONE) {
			handle_update(JSON.parse(xhr.responseText));
		}
	}
	xhr.open('GET', 'ajax.php', true);
	xhr.send(null);
}

function handle_update(msg) {
	
	data = msg;
	
	refresh_table();
//	update_statusbar()4545156;
}

function update_statusbar() {
	var most = new Date();
	document.getElementById("updated").innerHTML = "Frissítve: " + most.toLocaleString();
}


function refresh_table() {
	
	var tmp = "";
	tmp += "<table>";
	
	var oszlopok = [
		"Auto indulás",
		"FERT",
		"BU",
		"Hiányzó",
		'Palettázandó'
	];
	
	var oszlopok_sorrendben = [
		"indulasi_ido",
		"material",
		"businessunit",
		"missingqty",
		"paletteqty",
	];
	
	tmp += "<tr>";
	oszlopok.forEach(function(d) {
		tmp += "<th class=\""+ d + "\">" + d + "</th>";
	});
	tmp += "</tr>";
	
	
	
	var most = new Date();
	data.forEach(function(d) {
		var idopont = d["deptime_diff_in_secs"];
		var szin = "zold";

        if(idopont < 60*60) {
            szin = "sarga";
        }
        if(idopont < 0) {
            szin = "piros";
        }
         
/*         
		if((most - idopont + 60 * 60 * 1000) > 0) {
			szin = "sarga";
		}
		
		if((most - idopont) > 0) {
			szin = "piros";
		}
*/	
		var sor = "<tr class=\"" + szin + "\">";
		oszlopok_sorrendben.forEach(function(oszlop) {
			sor += "<td class=\"" + oszlop + "\">"  + d[oszlop] + "</td>";
		});
		sor += "</tr>";
		tmp += sor;
	});
	tmp += "</table>";
	document.getElementById("tablazat").innerHTML = tmp;
}
</script>
</body>
</html>