<?php 
$nm_lokasi = ["Rumah", "SMK PGRI", "KOKOPELLI", "POLINDRA", "GLAYEM", "RS MITRA"];
$lokasi = ["-6.5557412,108.2570063", "-6.4670497,108.2968779","-6.3369851,108.3317651","-6.4084094,108.2772245","-6.4213077,108.4310281","-6.4601844,108.2826717"];
$dist_matrix = [[0,15.245,34.787,23.519,37.848,15.208],[14.357,0,16.949,12.619,24.425,4.308],[30.57,16.472,0,12.855,15.139,19.226],[20.93,8.48,15.436,0,27.039,6.37],[37.948,24.515,15.144,24.887,0,28.564],[15.432,2.982,19.789,8.521,27.115,0]];

for($i=0;$i<count($lokasi); $i++){
	for($j=0;$j<count($lokasi);$j++){
		//$dist_matrix[$i][$j] = get_distance($lokasi[$i],$lokasi[$j]);//distance2(explode(",",$lokasi[$i])[0],explode(",",$lokasi[$i])[1],explode(",",$lokasi[$j])[0],explode(",",$lokasi[$j])[1]);
	}
}

function get_distance($origins,$dest){
	$key = "AvtRytS_MfDSA7wETymwrsi1aT0DRqXCHAvU11c8-QA5tMRVbQo0I_ZqxbjK4f7R";
    $ch = curl_init(); 

    // set url 
    curl_setopt($ch, CURLOPT_URL, "https://dev.virtualearth.net/REST/v1/Routes/DistanceMatrix?origins=".$origins."&destinations=".$dest."&travelMode=driving&o=json&key=".$key."&distanceUnit=km");

    // return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    // $output contains the output string 
    $output = curl_exec($ch); 

    // tutup curl 
    curl_close($ch);      

    // menampilkan hasil curl
   return json_decode($output)->resourceSets[0]->resources[0]->results[0]->travelDistance;
}
?>
<h1>Distance Matrix</h1>
<table border>
	<tr>
		<td>
		#
		</td>
		<?php foreach($nm_lokasi as $lok){?>
		<td>
			<?=$lok?>
		</td>
		<?php } ?>
	</tr>
		<?php
		$html = "";
			for($i=0; $i<sizeof($lokasi); $i++){
				$html .= "<tr><td>$nm_lokasi[$i]</td>";
				for($j=0; $j<sizeof($lokasi); $j++){
					$html .= "<td>".$dist_matrix[$i][$j]."</td>";
				}
				$html .= "</tr>";
			}
		echo $html;
		?>
</table>