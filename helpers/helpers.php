<?php 

function base_url()
{
  return BASE_URL;
}

function media()
{
	return BASE_URL."/assets";
}

function formatMoney($cantidad)
{
	$cantida = number_format($cantidad,2,SPD,SPM);
	return $cantidad;
}


function nombre()
{
    return  NOMBRE;
}

function logo()
{
    return  LOGO;
}

function corto()
{
    return  CORTO;
}

function empresa()
{
    return  EMPRESA;
}

?>