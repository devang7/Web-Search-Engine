<?php

include 'spellCorrector.php';
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ERROR | E_PARSE);
require_once('Apache/Solr/Service.php');
require_once('Snippet.php');


?>
<!DOCTYPE html>
<html>
  <head>
    <title>PageRank vs lucene</title>
  </head>
  <style>
  .search_block
  {
    margin: 0;
    width: 100%;
    /*border: 3px solid #73AD21;*/
    padding: 10px;
  }
  .box
  {
    margin: auto;
    width: 100%;
    /*border: 3px solid #73AD21;*/
    padding: 10px;
  }
  .search{
    display: block;
    text-align: left;
    line-height: 150%;
    font-size: .85em;
  }
  .input_box{
    text-align: left;
  }
  .list
  {
    list-style-type: decimal;
    list-style-position: outside;
  }
  .title
  {
    text-transform: capitalize;
  }
  .desc
  {
    text-decoration: none;
    color: green;
  }
  </style>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
  <script>
  function myFunction(val)
  {
      var url = "suggest.php?data="+val.trim();
      //alert(url);
      $.get(url,function(data){
      document.getElementById("display").innerHTML = data;});
  }
  </script>
  <body>
    <form  accept-charset="utf-8" method="get">
     <!-- <div class = "input_box">-->
      <label class = "search" align: "centre" for="q">Search:</label>
      <input list = "display" id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" onkeyup = "myFunction(this.value)"/>

      <datalist id = "display">
      </datalist>

      <input type = "radio" name = "algo" value = "lucene" <?php if(isset($_GET['algo']) && $_GET['algo'] == "lucene") echo 'checked = "checked"';?>>Lucene
      <!--<input type = "radio" name = "algo" value = "PageRank" <?php if(isset($_GET['algo']) && $_GET['algo'] == "PageRank") echo 'checked = "checked"';?>>PageRank -->
      <input type="submit"/>
     <!-- <div/>-->
    </form>
<?php



// make sure browsers see this page as utf-8 encoded HTML


$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$map_file_name = "mapNYTimesDataFile.csv";
$file = fopen($map_file_name,"r");
$hashSet = array();
$overlap_count = 0;
$overlap_links = array();
$counter = 0;
$path = "/Users/devangjhaveri/Sites/NYTimesDownloadData/";
$core = "devang";
while(($f = fgetcsv($file)) != FALSE)
{
     $dict[$path.$f[0]] = $f[1]; 
}
if ($query)
{

  $word_list = explode(" ", $query);
  $query_correct = "";
  foreach ($word_list as $word) {

    $query_correct .= spellCorrector::correct($word)." ";
    # code...
  }
  $query = $query_correct;

 
  
 
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/'.$core);
  
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  //$addParam = array("sort" => "pageRankFile.txt desc");

  try
  {
    $results = $solr->search($query, 0, $limit);
    //$results_page = $solr->search($query, 0, $limit,$addParam);
  }
  catch (Exception $e)
  {
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
$count = 1;
foreach($results->response->docs as $doc)
{
  foreach($doc as $field => $value)
  {
    if(strcmp($field, "id") == 0)
    {
      $hashSet[$value] = true;
      $overlap_links[$value] = $count;
    }
  }
  $count += 1;
}
}

$algo = $_GET["algo"];

//echo $algo;

if ($results && strcmp($algo, "lucene") == 0)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
     <p> <?php echo "Showing Results for"." = ".$query; ?> </p>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol class = "list">
<?php
  $count = 1;

  foreach ($results->response->docs as $doc)
  {
     $link = "";
     $desc = "";
     $title - "";
     $id = "";
?>
      <li class = "list">
<?php

  
  foreach($doc as $field => $value)
  {
    if(strcmp($field, "id") == 0)
    {
      $id = $value;
      $link = $dict[$value];
      //echo $link;
       
    }
    if(strcmp($field, "og_description") == 0)
    {
      $desc = $value;
    }
    if(strcmp($field, "title") == 0)
    {
        $title = $value;
    }   
  } 
  $snip = Snippet::getSnippet($query,$id);
  if(strlen($snip.trim(" ")) == 0)
    $snip = $desc;

   ?>
            <div class = "search_block">
            <a class = "title" target = "_blank" href = <?php echo htmlspecialchars($link, ENT_NOQUOTES, 'utf-8'); ?>>  <?php echo $title; $count += 1; ?></a>
            <br/>
            <a class = "desc" href = <?php echo $link ?>> <?php echo $link ?> </a>
            <br/>
            <?php echo $id ?>
            <p><?php if(strcmp($desc,"") == 0) echo "N/A"; else echo $desc; ?></p>
            <p><i><?php if(strcmp($snip,"") == 0) echo "N/A"; else echo $snip; ?></i> </p>  
            <div/>
      </li>
  <?php
  }
?>
    </ol>
<?php
}
?>
    
  </body>
</html>