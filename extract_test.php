<?php
	include('simple_html_dom.php');
?>

<pre>
	<?php print_r( file_get_html('http://www.transalvador.salvador.ba.gov.br/?pagina=onibus/onibus')); ?>
</pre>