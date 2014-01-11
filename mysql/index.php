<?php 
//console.php
//  An itty-bitty DB Console written in PHP.
//  Copyright 2014 By [Tony Boyles]<AABoyles@gmail.com> (http://aaboyles.com)
//  Licensed under the GPL v3 or later.
 
//Edit these Credentials to match your Oracle Database's
$dbuser = '';
$dbpass = '';
$dbhost = '127.0.0.1';
$dbport = '';
$dbname = '';
 
$print = FALSE;
if(isset($_REQUEST['query'])){
	$db = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	$res = $db->query($query);
	if (isset($_REQUEST['data'])) {
		header('Content-type: application/json');
		exit(json_encode($res));
	}
	$print = TRUE;
}
 
?>
 
<!DOCTYPE html>
<html>
	<head>
		<title>MySQL Console</title>
		<script type="application/javascript" src="//code.jquery.com/jquery-2.0.3.min.js"></script>
		<style type="text/css">
			html {
				font-family: sans-serif;
				-ms-text-size-adjust: 100%;
				-webkit-text-size-adjust: 100%;
			}
			body {
				margin: 0;
				padding: 0 2%;
			}
			textarea {
				resize: none;
			}
			h1, h2{
				text-align:center;
				margin: 0;
				padding: 0;
			}
			#left {
				width: 49%;
				float: left;
			}
			#query {
				height:100px;
				width:100%;
			}
			#permalink {
				float:right;
			}
			#history {
				text-align:left;
			}
			#right{
				width:49%;
				float:right;
			}
			#JSON {
				width:100%;
				height:450px;
				display:none;
			}
			#Table {
				border:1px solid black;
				width:100%;
				height:450px;
				text-align:left;
				overflow:auto;
			}
			table {
				border-collapse:collapse;
				min-height:100%;
			}
			th {
				background:#bbbbbb;
			}
			th, td{
				border:1px solid black;
			}
			#resultFormat {
				float: left;
			}
			#download {
				float:right;
			}
		</style>
	</head>
	<body>
		<h1>MySQL Database Console</h1>
		<div id="left">
			<h2>Query</h2>
			<textarea id="query"><?php if($print){ echo $_REQUEST['query']; } ?></textarea>
			<input id="submit" type="button" value="Submit Query" /><input id="force" type="button" value="Force Update" />
			<a id="permalink">Permalink to this Query</a>
 
			<h2>History</h2>
			<div id="history"></div>
		</div>
 
		<div id="right">
			<h2>Results</h2>
			<textarea id="JSON" class="result-pane" readonly="readonly"><?php if($print){echo json_encode($res, JSON_PRETTY_PRINT);} ?></textarea>
			<div id="Table" class="result-pane">
				<table><?php
				if($print){
					echo "<thead><tr><th>" . implode("</th><th>", array_keys($res[0])) . "</th></tr></thead><tbody>";
					foreach($res as $result){
						echo "<tr><td>" . implode("</td><td>",array_values($result)) . "</td></tr>";
					}
					echo "</tbody>";
				}
				?></table>
			</div>
			<select id="resultFormat">
				<option>JSON</option>
				<option selected="selected">Table</option>
			</select>
			<a id="download">Download These Results</a>
		</div>
		<script type="application/javascript">
			$(function() {
				queries = {<?php if($print){echo "'" . $_REQUEST['query'] . "': " . json_encode($res);} ?>};
 
				function queryDB(query){
					return $.ajax({
						url: "",
						cache: false,
						data : {
							data: true,
							query : query
						},
						success: function(data) {
							queries[query] = data;
						}
					});
				}
 
				function updateResults(data, addHistory) {
					if (typeof addHistory === "undefined") {
						addHistory = true;
					}
					$("#JSON").text(JSON.stringify(data, null, 4));
					var table = "<thead><tr><th>" + Object.keys(data[0]).join("</th><th>") + "</th></thead><tbody>";
					$.each(data, function(ind, el) {
						table += "<tr>";
						$.each(el, function(key, val) {
							table += "<td>" + val + "</td>";
						});
						table += "</tr>";
					});
					$("#Table table").html(table + "</tbody></table>");
					if (addHistory) { addToHistory(); }
					if($("#resultFormat").val()=="Table"){
						setTableLink();
					} else {
						setJSONLink();
					}
					$("#permalink")[0].href=window.location.origin+window.location.pathname+"?query="+$("#query").val();
				}
 
				function addToHistory(query){
					if(typeof query === "undefined"){
						query = $("#query").val();
					}
					$("<a href='#'>" + query + "</a><br />").click(function(evt) {
						updateResults(queries[query], false);
					}).appendTo("#history");
				}
 
				function setTableLink(){
					var link = $("#download");
					var data = queries[$("#query").val()];
					var csv = Object.keys(data[0]).join(",") + "\n";
					$.each(data, function(ind, el) {
						$.each(el, function(key, val) {
							csv += val + ",";
						});
						csv = csv.substring(0, csv.length-2) + "\n";
					});
					csv = csv.substring(0, csv.length-2);
					link[0].href="data:application/csv;charset=utf-8," + encodeURIComponent(csv);
					link[0].download="QueryResults.csv";
				}
 
				function setJSONLink(){
					var link = $("#download");
					var data = queries[$("#query").val()];
					link[0].href="data:application/json;charset=utf-8," + encodeURIComponent(data);
					link[0].download="QueryResults.json";
				}
 
				$("#submit").click(function() {
					var query = $("#query").val();
					if (queries.hasOwnProperty(query)) {
						updateResults(queries[query], false);
					} else {
						queryDB(query).done(updateResults);
					};
				});
 
				$("#force").click(function(){
					var query = $("#query").val();
					queryDB(query).done(updateResults);
				});
 
				$("#resultFormat").change(function() {
					var val = this.value;
					$(".result-pane").fadeOut(400, function() {
						$("#" + val).fadeIn();
					});
					var link = $("#download");
					var data = queries[$("#query").val()];
					switch(val){
						case "Table":
							setTableLink();
							break;
						case "JSON":
						default:
							setJSONLink();
					}
				});
 
				<?php if($print){ echo "updateResults(" . json_encode($res) . ");"; }?>
 
			});
		</script>
	</body>
</html>