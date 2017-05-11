<?php
require_once("simple_html_dom.php");
//Snippet::getSnippet("brexit","/Users/devangjhaveri/Sites/NYTimesDownloadData/a0393c59-dc60-429c-b5df-18ea8d54d7c6.html");
class Snippet
{
	public static function getSnippet($query,$url){

			$key = explode(" ", $query);
			$keyword = $key[0];
			
			$output = "";
			$count = 0;


			
			$content = file_get_html($url)->plaintext;

			$pos = stripos($content, $keyword);
			//echo $content;
			if($pos === false)
				return $output;
			
			for($i = $pos - 50; $i < strlen($content) && $i >= 0 && $count <= 160; $i++)
			{
				if(ctype_alpha($content[$i]) || strcmp($content[$i]," ") == 0){
					$output .= $content[$i];
					$count++;
				}
			}
			$output = $output.trim(" ");
			$count = strlen($output);
			if(strlen($output) == 0)
				return $output;
			
			$meta = get_meta_tags($url);
			foreach($meta as $m){
				if(stripos($m,$keyword) !== false){
					if($count >= 160)
						return $output;
					$output .= "...".$m;
					$count += strlen($m);
					if($count >= 160)
						return $output;
					
				}
			}
			return "... ".$output."...";

		}


    
}

?>