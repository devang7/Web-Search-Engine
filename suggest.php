
<?php
require_once('Apache/Solr/Service1.php');
      $solr_suggest = new Apache_Solr_Service1('localhost', 8983, '/solr/'.'devang');
      $query = strtolower($_GET["data"]);
      $limit = 10;
      $results = $solr_suggest->search($query, 0, $limit);
      $final_ans = "";
      foreach($results->suggest->suggest->$query->suggestions as $doc)
      {
        $ans = '<option value = "';
          foreach($doc as $field => $value)
          {
              
              if(strcmp($field, "term") == 0)
              {   
                    //  echo $value;
                    $ans .= $value.'"/>';
              }
          }
          $final_ans = $final_ans.$ans;

      }
      echo ($final_ans);  
?>