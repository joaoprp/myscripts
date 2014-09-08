<pre>
<?php
	include('simple_html_dom.php');

	$url = 'http://www.transalvador.salvador.ba.gov.br/paginas/onibus/consulta_linha/consulta_linha_load.php';
	
	$page = getDomParsedHtml($url,true, array('index'=>2));
	//print_r($page->find('option')->innertext);
	$options = $page->find('option');
	print_r($options);die();
	$count = count($options);
	for ($i=1; $i < $count-($count-2); $i++) { 
		echo $options[$i]->innertext.'<br>';

		$url = 'http://www.transalvador.salvador.ba.gov.br/paginas/onibus/consulta_linha/tabela_load.php';// carrega tabela de linhas por bairro
		$post = array(
						'index' =>  '2',
		                'campo' => $options[$i]->innertext,
		                'inicio' => '1',
		                'fim' => '500',
		                'pAtual' => '1',
		             );

		$page = getDomParsedHtml($url, true, $post);
		foreach ($page->find('tr') as $line) {
			$tds = $line->find('td');
			if($tds){
				$linha_cod = $tds[0]->innertext;
				$linha_nome = $tds[1]->innertext;

				//Checar se a linha já não foi inserida
				$row = findRow('linhas', "id LIKE '".$linha_cod."'");
				if(!$row){
					//Case não tenha sido ainda, inserir dados, horarios e itinerários
					getItinerarios($linha_cod);
					getHorarios($linha_cod);
				}
			}
		}

	}
	
	function getHorarios($codigo, $codigoFilha='00'){
		// Pega Horário:http://www.transalvador.salvador.ba.gov.br/paginas/onibus/consulta_linha/consultar_horario.php?codigo=I002&codigoFilha=00
		// Request Method:GET
		// Status Code:200 OK
		echo "getting Horario: Start <br>";

		$tipos_de_dias = array(
				'uteis' => 1,
				'sabado' => 2,
				'domingo' => 3,
			);

		foreach ($tipos_de_dias as $key => $dia) {

			$url = 'http://www.transalvador.salvador.ba.gov.br/paginas/onibus/consulta_linha/consultar_horario.php?codigo='.$codigo.'&codigoFilha='.$codigoFilha.'&tipoDia='.$dia.'&acao=CONSULTAR_HORARIO';
			$page = getDomParsedHtml($url);
			
			$ida = $page->find('div[id=content] table[width=578]',0);
			if($ida){
				$manha = $ida->find('td[bgcolor=#6699CC] div');
				$m = array();
				foreach ($manha as $time) {
					if($time->plaintext) $m[] = $time->plaintext;
				}
				$manha = implode(',', $m);

				$tarde = $ida->find('td[bgcolor=#eaedf4] div');
				$t = array();
				foreach ($tarde as $time) {
					if($time->plaintext) $t[] = $time->plaintext;
				}
				$tarde = implode(',', $t);

				$noite = $ida->find('td[bgcolor=#999CC] div');	
				$n = array();
				foreach ($noite as $time) {
					if($time->plaintext) $n[] = $time->plaintext;
				}
				$noite = implode(',', $n);

				$times = implode(',', array($manha, $tarde, $noite));
				// echo 'Ida- > '.$times.'<br>';
				if($times != ',,'){
					addTime($codigo, $times, $dia, 1 );
				}
			}
			
			$volta = $page->find('div[id=content] table[width=548]',5);
			// print_r($page->find('div[id=content] table[width=548]')->innertext);
			if($volta){
				$manha = $volta->find('td[bgcolor=#6699CC] div');
				$m = array();
				foreach ($manha as $time) {
					if($time->plaintext) $m[] = $time->plaintext;
				}
				$manha = implode(',', $m);

				$tarde = $volta->find('td[bgcolor=#eaedf4] div');
				$t = array();
				foreach ($tarde as $time) {
					if($time->plaintext) $t[] = $time->plaintext;
				}
				$tarde = implode(',', $t);

				$noite = $volta->find('td[bgcolor=#999CC] div');	
				$n = array();
				foreach ($noite as $time) {
					if($time->plaintext) $n[] = $time->plaintext;
				}
				$noite = implode(',', $n);

				$times = implode(',', array($manha, $tarde, $noite));
				//echo 'Volta- > '.$times.'<br>';
				if($times != ',,'){
					addTime($codigo, $times, $dia, 2 );
				}
					
			}	
		}

		echo "getting Horario: Done <br>";
	}

	function getItinerarios($codigo, $codigoFilha='00'){
		//Pega Itinerário http://www.transalvador.salvador.ba.gov.br/paginas/onibus/consulta_linha/consultar_itinerario.php?codigo=I002&codigoFilha=00
		// Request Method:GET
		// Status Code:200 OK
		$url = 'http://www.transalvador.salvador.ba.gov.br/paginas/onibus/consulta_linha/consultar_itinerario.php?codigo='.$codigo.'&codigoFilha='.$codigoFilha;
		$page = getDomParsedHtml($url);

		echo "getting Itinerario: Start <br>";

		$linha_nome = $page->find('span[class=tit02]',0)->plaintext;
		$empresas = $page->find('span[class=tit02]',1)->plaintext;

		//Cria a Linha
		$linha = addLinha($codigo, $linha_nome, $empresas);
		// $linha = false;
		if($linha){
			//Ida
			$table_ida = $page->find('table[bordercolor=#4589D0]',0);
			//print_r($table_ida->innertext);
			$itens = $table_ida->find('span[class=style28]');
			$i=1;
			foreach ($itens as $item) {
				echo 'Adding -> '.$item->innertext.'<br>';
				//$item => "Rua - Bairro"
				$address = explode(' - ', $item->innertext);
				
				//Add a new neighborhood or Find the neighborhood_id
				$bairro = addBairro($address[1]);
				//Add a new street or Find the street_id
				$rua = addRua($address[0], $bairro);
				//Add the street to the Bus Line's Flow
				$flow = addFlow($codigo, $rua, $i, 1);

				$i++;
			}

			//Volta
			$table_volta = $page->find('table[bordercolor=#4589D0]',1);
			//print_r($table_volta->innertext);
			$itens = $table_volta->find('span[class=style28]');
			$i=1;
			foreach ($itens as $item) {
				// echo 'Adding -> '.$item->plaintext.'<br>';
				//$item => "Rua - Bairro"
				$address = explode(' - ', $item->plaintext);
				
				//Add a new neighborhood or Find the neighborhood_id
				$bairro = addBairro($address[1]);
				//Add a new street or Find the street_id
				$rua = addRua($address[0], $bairro);
				//Add the street to the Bus Line's Flow
				$flow = addFlow($codigo, $rua, $i, 2);
				
				$i++;
			}	
		}
		echo "getting Itinerario: Done <br>";
	}

	function addLinha($cod, $name, $empresas=''){
		if($cod && $name){
			$db = mysql_connect('localhost', 'root', 'root');
			
			mysql_select_db('transalvador_data');

			$insert = 	"INSERT INTO linhas (id, nome, empresa) ".
						"VALUES ('$cod', '$name', '$empresas');";
			
			$result = mysql_query($insert);
			mysql_close($db);

			return $result;
		}
		return false;	
	}
	function addBairro($bairro){
		if($bairro){
			$row = findRow('bairros', "bairro LIKE '".$bairro."'");
			if(!$row){
				$db = mysql_connect('localhost', 'root', 'root');
			
				mysql_select_db('transalvador_data');

				$insert = 	"INSERT INTO bairros (bairro, regiao_id) ".
							"VALUES ('$bairro', 1);";
				
				mysql_query($insert);
				
				$result = mysql_insert_id();

				mysql_close($db);

				return $result;
			}else{
				return $row[0]->id;
			}	
		}
		return null;
		
	}
	function addRua($rua, $bairro_id){
		if($bairro_id && $rua){
			$row = findRow('ruas', "rua LIKE '".$rua."' AND bairro_id = ".$bairro_id);
			if(!$row){
				$db = mysql_connect('localhost', 'root', 'root');
			
				mysql_select_db('transalvador_data');

				$insert = 	"INSERT INTO ruas (rua, bairro_id) ".
							"VALUES ('$rua', $bairro_id);";
				
				mysql_query($insert);

				$result = mysql_insert_id();

				mysql_close($db);

				return $result;
			}else{
				return $row[0]->id;
			}	
		}
		return null;
	}
	function addFlow($linha_id, $rua_id, $step, $flow){
		if($linha_id && $rua_id && $step && $flow){
			$row = findRow('ruas', "rua_id = ".$rua_id." AND linha_id LIKE '".$linha_id."' AND step = ".$step." AND fluxo = ".$flow);
			if(!$row){
				$db = mysql_connect('localhost', 'root', 'root');
			
				mysql_select_db('transalvador_data');

				$insert = 	"INSERT INTO percursos (rua_id, linha_id, step, fluxo) ".
							"VALUES ($rua_id, '$linha_id', $step, $flow);";
				
				mysql_query($insert);

				$result = mysql_insert_id();

				mysql_close($db);

				return $result;
			}else{
				return $row[0]->id;
			}	
		}
		return null;
	}

	function addTime($linha_id, $times, $days, $flow){
		if($linha_id && $times && $days && $flow){
			$row = findRow('horarios', "horarios LIKE '".$times."'' AND linha_id LIKE '".$linha_id."'' AND dias = ".$days." AND fluxo = ".$flow);
			if(!$row){
				$db = mysql_connect('localhost', 'root', 'root');
			
				mysql_select_db('transalvador_data');

				$insert = 	"INSERT INTO horarios (horarios, linha_id, dias, fluxo) ".
							"VALUES ('$times', '$linha_id', $days, $flow);";
				
				mysql_query($insert);
				echo $insert.'<br>';
				$result = mysql_insert_id();

				mysql_close($db);

				return $result;
			}else{
				return $row[0]->id;
			}	
		}
		return null;
	}

	function findRow($table, $where){
		if($where && $table){
			$db = mysql_connect('localhost', 'root', 'root');
			
			mysql_select_db('transalvador_data');
			$query = 	"SELECT * FROM $table ".
						"WHERE $where;";
			
			$result = mysql_query($query);

			mysql_close($db);

			if (!(mysql_num_rows($result) == 0)) {
				$data = array();
				while ($row = mysql_fetch_object($result)) {
				    $data[] = $row;
				}
				return $data;
			}
		}
		return null;
	}

	function getDomParsedHtml($url='',$post=false,$post_data=array()){
		$curl = curl_init();
		$postData = $post_data;
		$result = null;
		$httpResponse = null;

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, $post);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

		$result = curl_exec($curl);

		$httpResponse = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($httpResponse == '404') {
		    throw new exception('This page doesn\'t exists.');
		}
		curl_close($curl);

		//echo $result;
		$html = str_get_html($result);

		return $html;		
	}
	
?>
</pre>