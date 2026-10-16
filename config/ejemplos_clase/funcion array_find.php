<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
<?php

$medidas = ["32.6","15.8","10.3","17.9"];

/*array_find, disponible en php 8.4 o mas devuelve el primer elemento del array que
 cumple con la condicion indicada por la funcion callback si no encuentra devuelve null*/
/*condicion mayor que o menos que o igual o mayoror que 100 como no hay nos da null*/

$medida = array_find($medidas,
/*2º argumento es una funcion call_back que prueba la condicion para cada uno de los elementos*/ 
fn ($medida) =>$medida >= 17

);

var_dump($medida);

