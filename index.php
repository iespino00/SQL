<?php
include("../connection.php");

getData($odbc);

function getData($odbc)
{
    $stmt = odbc_exec( $odbc ,"
        select EMPLEADOS.nomina,EMPLEADOS.nombre,EMPLEADOS.apellido_paterno,EMPLEADOS.apellido_materno,
            ESTRUCTURA.nombre_estructura, tipo_empleado
        from EMPLEADOS INNER JOIN ESTRUCTURA ON EMPLEADOS.depto = ESTRUCTURA.depto INNER JOIN PUESTOS ON EMPLEADOS.puesto = PUESTOS.puesto
        where (nomina >= 20000000 and nomina <= 30000000) and (tipo_empleado = 'C' OR tipo_empleado = 'S') and nomina_procesada = 1 ORDER BY nomina ASC");

    $array = array();
    $ind=0;

    while($row=odbc_fetch_array($stmt))
    {                   
                $auth['nomina'] = $row['nomina'];  
                if($row['tipo_empleado']=='C')
                {
                    $auth['tipo_empleado'] = 'No Sindicalizado';
                }
                else
                {
                    $auth['tipo_empleado'] = 'Sindicalizado';
                }
                    $auth['estructura']= $row['nombre_estructura'];
                    $auth['nombre']= $row['nombre']." ".$row['apellido_paterno']." ".$row['apellido_materno'];        
                    $array[$ind] =  $auth ;
                    $ind++;
    }

    utf8_converter($array);
//     createJson($array);
  
}

function createJson($array)
{
    $jsonencoded = json_encode($array,JSON_UNESCAPED_UNICODE);
    $fh = fopen("empleados.json", 'w');
    fwrite($fh, $jsonencoded);
    fclose($fh);
    echo "Archivo JSON Creado";
    updateRepository();
}



function utf8_converter($array)
{
    array_walk_recursive($array, function(&$item, $key)
    {
        if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
        }
    });
 
    createJson($array);
}

function updateRepository()
{
    $fecha=date("d/m/Y,h-i-s");
    exec("git add .");
    exec('git commit -m "'.$fecha.'" ');
    exec('git push -u origin master');
    echo "Repositorio Actualizado";
}


?>