<pre>
<?php header ('Content-type: text/html; charset=ISO-8859-1'); ?>
<?php
	include('simple_html_dom.php');
	set_time_limit(0);

	$page = file_get_html2('http://www.guiamais.com.br/busca/bares-salvador-ba');
 	$baseUrl = "http://www.guiamais.com.br";

 	$pageCount=1;
	do {
		printf("<b>Pagina: %s </b><br>", $pageCount);
		$nextPageButton = $page->find('a.next',0);// Navegação na Paginação
		getPageContents($page, $baseUrl);
		sleep(4);
		$page = ($nextPageButton)?file_get_html($nextPageButton->href):null;
		$pageCount++;
	} while ($nextPageButton);
	printf("<b>Done!</b><br>");
	

	function getPageContents($currentPage, $baseUrl){
		
		foreach ($currentPage->find('h2.org') as $element) { // Navegação nos Resultados
		 	$url =  $baseUrl.$element->find('a', 0)->href;
		 	// printf("<b>url: %s </b><br>", html_entity_decode($url, ENT_NOQUOTES, 'UTF-8'));

		 	$item = file_get_html2($url);
		 	
		 	//Extração de dados
		 	$titulo = $item->find('div.adv h1.fn', 0);
		 	$titulo = ($titulo)?iconv(mb_detect_encoding($titulo->plaintext), "UTF-8", $titulo->plaintext):'nao disponivel;';
		 	// $titulo = ($titulo)?$titulo->plaintext:'nao disponivel;';

		 	$descricao = $item->find('div.newKnowMoreShow h3', 0);
		 	$descricao = $descricao?strip_tags($descricao->plaintext):'nao disponivel';

			$rua = $item->find('span.street-address a', 0);
		 	$rua = ($rua)?iconv(mb_detect_encoding($rua->plaintext), "UTF-8", $rua->plaintext):'nao disponivel;';

		 	$bairro = $item->find('span.district', 0);
		 	$bairro = ($bairro)?iconv(mb_detect_encoding($bairro->plaintext), "UTF-8", $bairro->plaintext):'nao disponivel;';

		 	$cidade = $item->find('span.locality', 0);
		 	$cidade = ($cidade)?iconv(mb_detect_encoding($cidade->plaintext), "UTF-8", $cidade->plaintext):'nao disponivel;';

		 	$estado = $item->find('span.region', 0);
		 	$estado = ($estado)?iconv(mb_detect_encoding($estado->plaintext), "UTF-8", $estado->plaintext):'nao disponivel;';
		 	
		 	$cep = $item->find('span.postal-code', 0);
		 	$cep = ($cep)?iconv(mb_detect_encoding($cep->plaintext), "UTF-8", $cep->plaintext):'nao disponivel;';
		 	
		 	$tel = $item->find('li.tel', 0);
		 	$tel = ($tel)?strip_tags($tel->plaintext):'nao disponivel;';

		 	$images = array();
		 	foreach ($item->find('img.advPhoto') as $image) {
		 		$images[$image->src] = $image->src;
		 	}
		 	

		 	$mysql = mysqli_connect('localhost','root','','scraping_guia_mais');
		 	$insertQuery = "INSERT INTO estabelecimento (nome, detalhes, rua, bairro, cidade, estado, cep, tel) 
		 					VALUES('".mysqli_real_escape_string($mysql,$titulo)."', '".mysqli_real_escape_string($mysql,$descricao)."', '".$rua."', '".$bairro."', '".$cidade."', '".$estado."', '".$cep."', '".$tel."')";
		 	$insertionStatus = (mysqli_query($mysql,$insertQuery))?'OK':'Failed';
		 	$estabelecimentoId = mysqli_insert_id($mysql);

		 	printf("Titulo: %s <br>", $titulo);
		 	printf("Rua: %s <br>", $rua);
		 	printf("Bairro: %s <br>", $bairro);
		 	printf("Cidade/Estado: %s/%s <br>", $cidade, $estado);
		 	printf("CEP: %s <br>", $cep);
		 	printf("Tel: %s <br>", $tel);
		 	printf("Descricao: %s <br>", $descricao);
		 	printf("<b>Save: %s </b><br>", $insertionStatus);
		 
		 	
		 	
		 	foreach ($images as $url) {
		 		$insertQuery = "INSERT INTO images (estabelecimento_id, url) 
		 					VALUES(".$estabelecimentoId.", '".$url."')";
		 		$insertionStatus = (mysqli_query($mysql,$insertQuery))?'OK':'Failed';
		 		printf("<b>Save Image: %s</b> %s<br>", $insertionStatus, $url);
		 	}
		 
		 	echo '<br><br>';
		 	mysqli_close($mysql);

		 
		 	die();
		}
		// die();
	}

?>
</pre>