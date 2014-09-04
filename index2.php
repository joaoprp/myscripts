<html>
	<body>
		<h1>It works!</h1>
		<?php
			 $pasta = dir(getcwd());

			 if(is_dir($pasta))
			 {
			  $diretorio = dir($pasta);

			  while($arquivo = $diretorio->read())
			  {
			   echo '<a href='.$pasta.$arquivo.'>'.$arquivo.'</a><br />';
			  }

			  $diretorio->close();
			 }
			 else
			 {
			  echo 'A pasta não existe.';
			 }
		?>
	</body>
</html>