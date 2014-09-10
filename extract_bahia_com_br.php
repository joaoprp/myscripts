<pre>
<?php
	include('simple_html_dom.php');
	set_time_limit(0);

	

	$page = file_get_html2('http://bahia.com.br/atracao/page/1/');
	// $baseUrl = 'http://bahia.com.br/atracao/';
 	
 	$pageCount=1;
	do {
		printf("<b>Pagina: %s </b><br>", $pageCount);
		$nextPageButton = $page->find('a.next',0);// Navegação na Paginação
		getPageContents($page);
		sleep(4);
		$page = ($nextPageButton)?file_get_html($nextPageButton->href):null;
		$pageCount++;
	} while ($nextPageButton);
	printf("<b>Done!</b><br>");
	

	function getPageContents($currentPage){
		
		foreach ($currentPage->find('li.pull-left') as $element) { // Navegação nos Resultados
		 	$url =  $element->find('a', 0)->href;
		 	// printf("<b>url: %s </b><br>", $url);
		 	// printf("<b>url: %s </b><br>", html_entity_decode($url, ENT_NOQUOTES, 'UTF-8'));

		 	$event = file_get_html2($url);

		 	//Extração de dados
		 	$titulo = $event->find('section.content h1', 0);
		 	$titulo = ($titulo)?htmlentities($titulo->plaintext,null,"UTF-8"):'nao disponivel;';

		 	$descricao = $event->find('section.content p', 0);
		 	$descricao = ($descricao)?htmlentities($descricao->plaintext,null,"UTF-8"):'nao disponivel;';

		 	$endereco = $event->find('section.content p', 1);
		 	$endereco = ($endereco)?htmlentities(($endereco->plaintext,null,"UTF-8"):'nao disponivel;';

		 	$horario = $event->find('section.content p', 2);
		 	$horario = ($horario)?htmlentities($horario->plaintext,null,"UTF-8"):'nao disponivel;';
			
		 	$mysql = mysqli_connect('localhost','root','','scraping_bahia_com_br');
		 	$insertQuery = "INSERT INTO atracoes (titulo, descricao, endereco, horario) 
		 					VALUES('".$titulo."', '".$descricao."', '".$endereco."', '".$horario."')";
		 	$insertionStatus = (mysqli_query($mysql,$insertQuery))?'OK':'Failed';
		 	mysqli_close($mysql);

		 	printf("Titulo: %s <br>", $titulo);
		 	printf("Descrção: %s <br>", $descricao);
		 	printf("Endereco: %s <br>", $endereco);
		 	printf("Horario: %s <br>", $horario);
		 	printf("<b>Save: %s </b><br>", $insertionStatus);

		 	echo '<br><br>';
		}
		// die();
	}

?>
</pre>
