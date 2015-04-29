<?php
/*
Author: Nyco Agung
Description: Rotten Tomatoes Search
Version: 1.0
*/

require 'settings.php';

//Function To Highlight Text
function func_highlight_string($haystack, $needles){
	//IF Search Multiple Keywords
	if(is_array($needles)){
		foreach($needles as $needle){
			if(strlen($needle)>3){ //If search string len > 3
				preg_match_all("/$needle+/i", $haystack, $matches);
				if (is_array($matches[0]) && count($matches[0]) >= 1) {
					foreach ($matches[0] as $match) {
						$haystack = str_replace($match, '<span class="highlight">'.$match.'</span>', $haystack);
					}
				}
			}
		}
	}else{ //IF Search Single Word
		if(strlen($needles)>3){ //If search string len > 3
			preg_match_all("/$needles+/i", $haystack, $matches);
			if (is_array($matches[0]) && count($matches[0]) >= 1) {
				foreach ($matches[0] as $match) {
					$haystack = str_replace($match, '<span class="highlight">'.$match.'</span>', $haystack);
				}
			}
		}
	}
	return $haystack;
}


//IF SEARCH
if(isset($_REQUEST['q']) && $_REQUEST['q'] != ''){ 
	if(isset($_REQUEST['page'])){
		$var_page = filter_var($_REQUEST['page'], FILTER_VALIDATE_INT);
	}else{
		$var_page = 1;
	}
	$var_test_mode = filter_var($_REQUEST['test_mode'], FILTER_VALIDATE_INT); //Test Mode or Live Mode (IF LIVE PLEASE EDIT inc/settings.php AND ENTER VALID API KEY)
	$var_page_limit = filter_var($_REQUEST['page_limit'], FILTER_VALIDATE_INT); //PAGE LIMIT
	$var_q = filter_var($_REQUEST['q'], FILTER_SANITIZE_STRING); //SANATIZE Q
	$var_qs = explode(' ', $var_q); //SPLIT INTO ARRAYS
	$var_url = 'http://api.rottentomatoes.com/api/public/v1.0/movies.json?apikey='.API_KEY.'&q='.urlencode($var_q).'&page_limit='.$var_page_limit.'&page='.$var_page; //ROTTEN TOMATOES API
	
	//CURL Rotten Tomatoes
	$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$var_url);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	$results_json = curl_exec($ch);
	curl_close($ch);
	
	//IF CURL FAILED
	if($results_json == ''){ $results_json = file_get_contents($var_url); }
	
	//TESTING
	if($var_test_mode == 1){
		$results_json = $results_json_test; //SET VALUE TO TEST
	}
	
	//JSON Decode
	$results = json_decode($results_json, true);	
	
	//HEADER
	echo 'SEARCH RESULTS FOR : "<strong>'.$var_q.'</strong>"';
	
	//IF ERROR
	if(isset($results['error'])){
		echo
		'<p>ERROR:'.$results['error'].'</p>';
		exit();
	
	}elseif(intval($results['total']) > 0){
		//Calculate the number of pages
		$total_pages = ceil($results['total']/$var_page_limit);
		
		echo
		'<p>Your search returned <strong>'.$results['total'].'</strong> results</p>';
		//Movies
		if(isset($results['movies']) && count($results['movies'])>0){
			echo '<h2>Movies</h2>';
			foreach($results['movies'] as $movie){
				echo 
				'<div class="movies block">
					<ul class="movie_list">
						<li class="poster"><img src="'.$movie['posters']['thumbnail'].'"></li>
						<li class="title"><a href="'.$movie['links']['alternate'].'" target="_blank">'.func_highlight_string($movie['title'], $var_qs).' ('.$movie['year'].') - ('.intval($movie['runtime']).'mins)</a></li>
						<li class="ratings"><span>Ratings</span>
							Critics Score: '.$movie['ratings']['critics_score'].' | 
							Audience Score: '.$movie['ratings']['audience_score'].'
						</li>
						<li cass="casts"><span>Casts</span>';
						foreach($movie['abridged_cast'] as $cast){
							$casts[] = func_highlight_string($cast['name'], $var_qs);
						}
						echo implode(', ',$casts);
						unset($casts);
				echo '
						</li>
						<li class="sypnosis"><span>Sypnosis</span>'.func_highlight_string($movie['synopsis'], $var_qs).'</li>
					</ul>
				</div>';	
			}
		}
		
		//TV - Assuming we get TV results
		if(isset($results['tv']) && count($results['tv'])>0){
			echo '<h2>TV</h2>';
			foreach($results['tv'] as $tv){
				echo 
				'<div class="tv block">
					<ul class="tv_list">
						<li class="poster"><img src="'.$tv['posters']['thumbnail'].'"></li>
						<li class="title"><a href="'.$tv['links']['alternate'].'" target="_blank">'.func_highlight_string($tv['title'], $var_qs).' ('.$tv['year'].') - ('.intval($tv['runtime']).'mins)</a></li>
						<li class="ratings"><span>Ratings</span>
							Critics Score: '.$tv['ratings']['critics_score'].' | 
							Audience Score: '.$tv['ratings']['audience_score'].'
						</li>
						<li cass="casts"><span>Casts</span>';
						foreach($tv['abridged_cast'] as $cast){
							$casts[] = func_highlight_string($cast['name'], $var_qs);
						}
						echo implode(', ',$casts);
						unset($casts);
				echo '
						</li>
						<li class="sypnosis"><span>Sypnosis</span>'.func_highlight_string($tv['synopsis'], $var_qs).'</li>
					</ul>
				</div>';	
			}
		}
		
		//Navigation
		if($var_page > 1){ $var_prev_page = ($var_page-1); }else{ $var_prev_page = $var_page; }
		$var_prev_url = 'inc/search.php?q='.$var_q.'&page_limit='.$var_page_limit.'&page='.$var_prev_page.'&test_mode='.$var_test_mode;
		if(($var_page + 1) <= $total_pages){ $var_next_page = ($var_page + 1); }else{ $var_next_page = $var_page; }
		$var_next_url = 'inc/search.php?q='.$var_q.'&page_limit='.$var_page_limit.'&page='.$var_next_page.'&test_mode='.$var_test_mode;
		
		echo 
		'<ul class="nav">
			<li class="prev">
			<!--<a href="'.$results['links']['self'].'">This page</a>-->
			<a href="#_self" id="prev_page" class="nav_page" rel="'.$var_prev_url.'">Previous Page ('.$var_prev_page.')</a>
			</li>
			<li class="self"><strong>'.$var_page.'</strong>/'.$total_pages.'</li>
			<li class="next">
			<!--<a href="'.$results['links']['next'].'">Next page</a>-->
			<a href="#_self" id="next_page" class="nav_page" rel="'.$var_next_url.'">Next Page ('.$var_next_page.')</a>
			</li>
		</ul>';
	}else{
		echo 
		'<p>Your search returned no result</p>';
	}
}
?>