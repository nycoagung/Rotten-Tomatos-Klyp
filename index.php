<?php
/*
Author: Nyco Agung
Description: Rotten Tomatoes Search
Version: 1.0
*/
require 'inc/settings.php';

?>


<!doctype html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" id='styles'  href="css/styles.css" type="text/css" media="all" />

<script type="text/javascript" src="//code.jquery.com/jquery-1.10.1.min.js"></script>
<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.12.0/jquery.validate.min.js"></script>
<title>Rotten Tomatoes - Search - by Nyco Agung</title>
</head>

<body>
	<div class="wrapper">
    	<div id="bar_search" class="bar_search block">
            <form id="search_form" name="search_form" method="post" novalidate="novalidate">
            <input id="q" type="text" name="q" value="" placeholder="search" class="search" size="50" required>
            <select id="page_limit" name="page_limit">
            	<option value="10">10/page</option>
                <option value="20">20/page</option>
                <option value="50">50/page</option>
            </select>
            <select id="test_mode" name="test_mode">
            	<option value="1">Test Mode</option>
                <option value="0">Live Mode</option>
            </select>
            <input id="search_button" type="image" src="img/icon_search.png" alt="Search" >
            </form>
        </div>
        <div id="search_results" class="block"></div>
    </div>
    
    <script type="text/javascript">
	$(document).ready(function() {
		$("#search_form").validate({
			submitHandler: function(form) {
				$('#search_results').html('<img src="img/ajax-loader.gif" class="loader" />').fadeIn(); 
				var input_data = $('#search_form').serialize();  
				$.ajax({  
					type: "POST",  
					url:  "inc/search.php",  
					data: input_data,  
					success: function(msg){  
						$('#search_results').html(msg).fadeIn(); 
					}  
				});
			}
		});
		
		//Prev Nav Clicked
		$('body').on('click', '#prev_page', function( event ) {
			$.get($('#prev_page').attr('rel'), function(data){
				$('#search_results').html(data).fadeIn(); 
			});
		});
		
		//Next Nav Clicked
		$('body').on('click', '#next_page', function( event ) {
			$.get($('#next_page').attr('rel'), function(data){
				$('#search_results').html(data).fadeIn(); 
			});
		});
	});
	</script>
    
</body>
</html>