<?php
    require_once "/vendor/autoload.php";
?>
<html>
<head><title>Visual RDF</title></head>
<body>
<h1>Visual RDF</h1>
<?php
  $id = '';
    if (isset($_POST['id'])) {
      $id = $_POST['id'];
      $uri = 'https://doi.org/'.$_POST['id'];
      $client = new EasyRdf_Http_Client($uri);
      $client->setHeaders('Accept','text/turtle');
      $response = $client->request();
      $responseBody = $response->getBody();
      $graph = new EasyRdf_Graph($uri);
      $graph->parse($responseBody, 'turtle');

      $rdf = $graph->toRdfPhp();
      $title = recursiveFind($rdf, 'http://purl.org/dc/terms/title');
      $title = isset($title[0]['value']) ? $title[0]['value'] : 'unknown';

      $date = recursiveFind($rdf, 'http://purl.org/dc/terms/date');
      $date = isset($date[0]['value']) ? $date[0]['value'] : '';

      $creatorsFullname = array();
      $creators = recursiveFind($rdf, 'http://purl.org/dc/terms/creator');
      foreach($creators as $creator){
        if(isset($creator['value'])){
          $creatorName = recursiveFind($rdf, $creator['value']);
          $creatorsFullname[] = recursiveFind($creatorName, 'http://xmlns.com/foaf/0.1/name');
        }
      }

      echo '<a href="'.$uri.'">'.$title.'</a> - '.$date;

      echo '<h2>Authors :</h2>';
      echo '<ul>';
      foreach ($creatorsFullname as $creatorFullname) {
        if(isset($creatorFullname[0]['value'])){
          echo '<li>'.$creatorFullname[0]['value'].'</li>';
        }
      }
      echo '</ul>';
    }

    function recursiveFind(array $haystack, $needle)
    {
        $iterator  = new RecursiveArrayIterator($haystack);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }
    }

?>
<div style="margin: 10px">
  <form method="post">
    <label for="id">Enter a DOI Identifier</label>
    <input id="id" type="text" name="id" value="<?php echo $id; ?>"/>
    <button type="submit">Envoyer</button>
  </form>
</div>
</body>
</html>
