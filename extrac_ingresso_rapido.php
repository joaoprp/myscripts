<pre>
<?php
	include('simple_html_dom.php');
	set_time_limit(0);
	$page = file_get_html('http://www.ingressorapido.com.br/eventos.aspx');
	$baseUrl = 'http://www.ingressorapido.com.br/';
 	//get files from the first page
 	getPageContents('eventos.aspx', $baseUrl);

	$pagination = array();
	foreach($page->find('a.linkPaginacao') as $nextPage)    // Navegação na Paginação
	{
		if(!isset($pagination[$nextPage->href])){
			$pagination[$nextPage->href] = $nextPage->href;
			getPageContents($nextPage->href, $baseUrl);
			sleep(4);
		}
	}

	function getPageContents($url, $baseUrl=''){
		$page = file_get_html($baseUrl.$url);
		foreach ($page->find('.boxDotBorder') as $element) { // Navegação nos Resultados
		 	$url =  $element->find('a', 0)->href;

		 	$event = file_get_html($baseUrl.$url);
		 	
		 	//Extração de dados
		 	$titulo = $event->find('#cphBody_lblTitulo', 0);
		 	$descricao = $event->find('#divRelease p', 0);
		 	$categoria = $event->find('#cphBody_lblTipoEvento', 0);
		 	$genero = $event->find('#cphBody_lblGenero', 0);
		 	$local = $event->find('#cphBody_lblLocal', 0);
		 	$cidade = $event->find('#cphBody_lblCidade', 0);
		 	$pais = $event->find('#cphBody_lblPais', 0);
		 	$endereco = $event->find('#cphBody_lblEndereco', 0);
		 	$horarios = $event->find('#cphBody_cmbApresentacao option');

		 	printf("Titulo: %s <br>", $titulo->plaintext);
		 	printf("Descrção: %s <br>", $descricao->plaintext);
		 	printf("Categoria: %s <br>", $categoria->plaintext);
		 	printf("Genero: %s <br>", $genero->plaintext);
		 	printf("Local: %s <br>", $local->plaintext);
		 	printf("Cidade: %s <br>", $cidade->plaintext);
		 	printf("Pais: %s <br>", $pais->plaintext);
		 	foreach ($horarios as $horario) {
		 		printf("Horario: %s <br>", $horario->plaintext);
		 	}
		 	echo '<br><br>';
		}
	}
?>
</pre>