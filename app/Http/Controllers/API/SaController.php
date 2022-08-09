<?php

namespace App\Http\Controllers\API;

use App\Models\Delivery;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Gudang;

class SaController extends Controller
{
    function sa_tsp(Request $request)
    {

        $delivery = Delivery::where('status', '!=', 'Selesai')->where('courier_id', $request->user()->id)->get();
        // dd($delivery[0]->partner);

        $gudang = Gudang::first();

        // $nm_lokasi = ["Rumah", "SMA SINDANG", "KOKOPELLI", "POLINDRA", "GLAYEM", "RS MITRA"];
        $nm_lokasi = ["gudang"];
        foreach ($delivery as $key => $value) {
            array_push($nm_lokasi, $value->id);
        }
        // dd($nm_lokasi);
        // $lokasi = ["-6.5557412,108.2570063", "-6.4670497,108.2968779","-6.3369851,108.3317651","-6.4084094,108.2772245","-6.4213077,108.4310281","-6.4601844,108.2826717"];
        $lokasi = ["$gudang->latitude,$gudang->longitude"];
        foreach ($delivery as $key => $value) {
            array_push($lokasi, $value->partner->latitude . ',' . $value->partner->longitude);
        }
        //  dd($lokasi);
        // $dist_matrix = [[0,15.245,34.787,23.519,37.848,15.208],[14.357,0,16.949,12.619,24.425,4.308],[30.57,16.472,0,12.855,15.139,19.226],[20.93,8.48,15.436,0,27.039,6.37],[37.948,24.515,15.144,24.887,0,28.564],[15.432,2.982,19.789,8.521,27.115,0]];
        $dist_matrix = [];

        for ($i = 0; $i < count($lokasi); $i++) {
            for ($j = 0; $j < count($lokasi); $j++) {
                $dist_matrix[$i][$j] = $this->calculateDistance(explode(",", $lokasi[$i])[0], explode(",", $lokasi[$i])[1], explode(",", $lokasi[$j])[0], explode(",", $lokasi[$j])[1]);
            }
        }
        // dd($dist_matrix);

        $tMax = 100;
        $tMin = 1;
        $cooling_rate = 0.9;
        $max_iteration = 50;

        $tNow = $tMax;
        $it = 1;

        //Membuat solusi X dari random lokasi
        $solution_X = $this->generateRandomSolution(sizeof($lokasi) - 1);

        //Menentukan awal dan akhir titik lokasi di titik yang sama
        array_push($solution_X, 0);
        array_unshift($solution_X, 0);

        // $dst = 0.0;
        // for($bus=0; $bus<count($solution_X)-1; $bus++){
        //     $departureNode = $solution_X[$bus];
        //     $nextNode = $solution_X[$bus+1];
        //     $dst += $dist_matrix[$departureNode][$nextNode];
        //     echo "<br>$departureNode $nextNode ".$dist_matrix[$departureNode][$nextNode]." $nm_lokasi[$departureNode] ==> $nm_lokasi[$nextNode] <br>";
        // }


        //Menghitung fitness solusi X
        $X_fitness =  $this->fitnessTSP($solution_X, $dist_matrix);

        // echo "<br>Distance: $dst KM";
        // echo "<br>Fitness: $X_fitness";
        while ($tNow > $tMin) {
            //Buat uniform random 0-1
            $uniform_rand = rand(0, 1000000) / 1000000;

            //Mencoba mencari solusi baru
            if ($uniform_rand <= 0.33) {
                $new_solution =  $this->swapSearch($solution_X);
            } elseif ($uniform_rand > 0.33 and $uniform_rand > 0.66) {
                $new_solution =  $this->insertSearch($solution_X);
            } else {
                $new_solution =  $this->twoOptSearch($solution_X);
            }

            //Menghitung fitness dengan solusi baru
            $X_new_fitness =  $this->fitnessTSP($new_solution, $dist_matrix);

            //Jika solusi baru lebih baik, maka rubah solusi X lama dengan solusi yang baru
            if ($X_new_fitness > $X_fitness) {
                $solution_X = $new_solution;
                $X_fitness = $X_new_fitness;
            }

            //Update iterasi
            $it++;

            //Update temperature
            if ($it == $max_iteration) {
                $tNow = $tNow * $cooling_rate;
                $it = 1;
            }
        }


        ///COBA
        // $fullResult = [];
        // for ($k = 0; $k < count($lokasi) * count($lokasi); $k++) {
        //     $resultSementara = [];
        //     $dst = 0;
        //     for ($bus = 0; $bus < count($solution_X) - 1; $bus++) {
        //         $departureNode = $solution_X[$bus];
        //         $nextNode = $solution_X[$bus + 1];
        //         $dst = $dst + $dist_matrix[$departureNode][$nextNode];
        //         // var_dump("Distance $dst");
        //         $deliveryFrom = Delivery::find($nm_lokasi[$departureNode]);
        //         $deliveryNext = Delivery::find($nm_lokasi[$nextNode]);
        //         $develiveryFromArray['delivery_from'] = $deliveryFrom;
        //         $deliveryNextArray['delivery_next'] = $deliveryNext;

        //         // $distanceArray['distance']= $dist_matrix[$departureNode][$nextNode];

        //         array_push($resultSementara ,[
        //             'form_gudang' => ($deliveryFrom) ? null : $gudang,
        //             'to_gudang' => ($deliveryNext) ? null : $gudang,
        //             'delivery_from' => $deliveryFrom,
        //             'delivery_next' => $deliveryNext,
        //             'distance' =>  $dist_matrix[$departureNode][$nextNode],
        //         ]);
        //         // var_dump("<br>$departureNode $nextNode ".$dist_matrix[$departureNode][$nextNode]." $nm_lokasi[$departureNode] ==> $nm_lokasi[$nextNode] <br>");
        //         // echo "<br>$departureNode $nextNode ".$dist_matrix[$departureNode][$nextNode]." $nm_lokasi[$departureNode] ==> $nm_lokasi[$nextNode] <br>";
        //     }
        //     // dd("Total Distance $dst");
        //     array_push($fullResult ,[
        //         // 'solution'=>  $resultSementara,
        //         'totalDistance'=>$dst,
        //     ]);
        // }
        // dd($fullResult);

        // dd( $resultSementara );






        ///////Sementara

        $resultFull = [];
        $dst = 0.0;
        for ($bus = 0; $bus < count($solution_X) - 1; $bus++) {
            $departureNode = $solution_X[$bus];
            $nextNode = $solution_X[$bus + 1];
            $dst += $dist_matrix[$departureNode][$nextNode];
            $deliveryFrom = Delivery::find($nm_lokasi[$departureNode]);
            $deliveryNext = Delivery::find($nm_lokasi[$nextNode]);
            $develiveryFromArray['delivery_from'] = $deliveryFrom;
            $deliveryNextArray['delivery_next'] = $deliveryNext;
            // $distanceArray['distance']= $dist_matrix[$departureNode][$nextNode];

            array_push($resultFull, [
                'form_gudang' =>( $deliveryFrom )?null: $gudang,
                'to_gudang' => ( $deliveryNext )?null: $gudang,
                'delivery_from' => $deliveryFrom,
                'delivery_next' => $deliveryNext,
                'distance' =>  $dist_matrix[$departureNode][$nextNode],
            ]);

            // var_dump("<br>$departureNode $nextNode ".$dist_matrix[$departureNode][$nextNode]." $nm_lokasi[$departureNode] ==> $nm_lokasi[$nextNode] <br>");
        }
        // dd($dst);

        return ResponseFormatter::success(  $resultFull, "Get Delivery Success");
        // echo "<br>Distance: $dst KM";
        // // echo "<br>X Fitness: $X_fitness";
        // echo "</pre>";

    }



    function sa_tsp2(Request $request)
    {

        $delivery = Delivery::where('status', '!=', 'Selesai')->where('courier_id', $request->user()->id)->get();
        // dd($delivery[0]->partner);

        $gudang = Gudang::first();

        // $nm_lokasi = ["Rumah", "SMA SINDANG", "KOKOPELLI", "POLINDRA", "GLAYEM", "RS MITRA"];
        $nm_lokasi = ["gudang"];
        foreach ($delivery as $key => $value) {
            array_push($nm_lokasi, $value->id);
        }
        // dd($nm_lokasi);
        // $lokasi = ["-6.5557412,108.2570063", "-6.4670497,108.2968779","-6.3369851,108.3317651","-6.4084094,108.2772245","-6.4213077,108.4310281","-6.4601844,108.2826717"];
        $lokasi = ["$gudang->latitude,$gudang->longitude"];
        foreach ($delivery as $key => $value) {
            array_push($lokasi, $value->partner->latitude . ',' . $value->partner->longitude);
        }
        //  dd($lokasi);
        // $dist_matrix = [[0,15.245,34.787,23.519,37.848,15.208],[14.357,0,16.949,12.619,24.425,4.308],[30.57,16.472,0,12.855,15.139,19.226],[20.93,8.48,15.436,0,27.039,6.37],[37.948,24.515,15.144,24.887,0,28.564],[15.432,2.982,19.789,8.521,27.115,0]];
        $dist_matrix = [];

        for ($i = 0; $i < count($lokasi); $i++) {
            for ($j = 0; $j < count($lokasi); $j++) {
                $dist_matrix[$i][$j] = $this->calculateDistance(explode(",", $lokasi[$i])[0], explode(",", $lokasi[$i])[1], explode(",", $lokasi[$j])[0], explode(",", $lokasi[$j])[1]);
            }
        }
        // dd($dist_matrix);

        $tMax = 100;
        $tMin = 1;
        $cooling_rate = 0.9;
        $max_iteration = 50;

        $tNow = $tMax;
        $it = 1;

        //Membuat solusi X dari random lokasi
        $solution_X = $this->generateRandomSolution(sizeof($lokasi) - 1);

        //Menentukan awal dan akhir titik lokasi di titik yang sama
        array_push($solution_X, 0);
        array_unshift($solution_X, 0);


        //Menghitung fitness solusi X
        $X_fitness =  $this->fitnessTSP($solution_X, $dist_matrix);

        // echo "<br>Distance: $dst KM";
        // echo "<br>Fitness: $X_fitness";
        while ($tNow > $tMin) {
            //Buat uniform random 0-1
            $uniform_rand = rand(0, 1000000) / 1000000;

            //Mencoba mencari solusi baru
            if ($uniform_rand <= 0.33) {
                $new_solution =  $this->swapSearch($solution_X);
            } elseif ($uniform_rand > 0.33 and $uniform_rand > 0.66) {
                $new_solution =  $this->insertSearch($solution_X);
            } else {
                $new_solution =  $this->twoOptSearch($solution_X);
            }

            //Menghitung fitness dengan solusi baru
            $X_new_fitness =  $this->fitnessTSP($new_solution, $dist_matrix);

            //Jika solusi baru lebih baik, maka rubah solusi X lama dengan solusi yang baru
            if ($X_new_fitness > $X_fitness) {
                $solution_X = $new_solution;
                $X_fitness = $X_new_fitness;
            }

            //Update iterasi
            $it++;

            //Update temperature
            if ($it == $max_iteration) {
                $tNow = $tNow * $cooling_rate;
                $it = 1;
            }
        }

        ///////Sementara

        $resultFull = [];
        $dst = 0.0;
        for ($bus = 0; $bus < count($solution_X) - 1; $bus++) {
            $departureNode = $solution_X[$bus];
            $nextNode = $solution_X[$bus + 1];
            $dst += $dist_matrix[$departureNode][$nextNode];
            $deliveryFrom = Delivery::find($nm_lokasi[$departureNode]);
            $deliveryNext = Delivery::find($nm_lokasi[$nextNode]);
            $develiveryFromArray['delivery_from'] = $deliveryFrom;
            $deliveryNextArray['delivery_next'] = $deliveryNext;
            // $distanceArray['distance']= $dist_matrix[$departureNode][$nextNode];

            array_push($resultFull, [
                'form_gudang' =>( $deliveryFrom )?null: $gudang,
                'to_gudang' => ( $deliveryNext )?null: $gudang,
                'delivery_from' => $deliveryFrom,
                'delivery_next' => $deliveryNext,
                'distance' =>  $dist_matrix[$departureNode][$nextNode],
            ]);

            // var_dump("<br>$departureNode $nextNode ".$dist_matrix[$departureNode][$nextNode]." $nm_lokasi[$departureNode] ==> $nm_lokasi[$nextNode] <br>");
        }
        // dd($dst);

        return [
            'result'=> $resultFull,
            'total_distance'=>$dst,
        ];
        // echo "<br>Distance: $dst KM";
        // // echo "<br>X Fitness: $X_fitness";
        // echo "</pre>";
    }

    public function getAllNih(Request $request)
    {
        $resultFull =[];

        $delivery = Delivery::where('status', '!=', 'Selesai')->where('courier_id', $request->user()->id)->get();
        // dd($delivery[0]->partner);

        $gudang = Gudang::first();

        // $nm_lokasi = ["Rumah", "SMA SINDANG", "KOKOPELLI", "POLINDRA", "GLAYEM", "RS MITRA"];
        $nm_lokasi = ["gudang"];
        foreach ($delivery as $key => $value) {
            array_push($nm_lokasi, $value->id);
        }
        // dd($nm_lokasi);
        // $lokasi = ["-6.5557412,108.2570063", "-6.4670497,108.2968779","-6.3369851,108.3317651","-6.4084094,108.2772245","-6.4213077,108.4310281","-6.4601844,108.2826717"];
        $lokasi = ["$gudang->latitude,$gudang->longitude"];
        foreach ($delivery as $key => $value) {
            array_push($lokasi, $value->partner->latitude . ',' . $value->partner->longitude);
        }

        for ($i=0; $i <200 ; $i++) { 
            // var_dump($this->sa_tsp2($request));
            array_push( $resultFull, $this->sa_tsp2($request));
        }

        $collection = collect($resultFull[0]);

        dd($collection['total_distance']->sort());

        // dd($collection->sort('total_distance')[0]['total_distance']);

        return ResponseFormatter::success($collection->sort()[0]['result'], "Get Distance Success");
    }



    function distance1($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'miles')
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        switch ($unit) {
            case 'miles':
                break;
            case 'kilometers':
                $distance = $distance * 1.609344;
        }
        return (round($distance, 5));
    }
    // function distance2($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'miles') {
    //   $rad_lat1 = deg2rad($latitude1);
    //   $rad_long1 = deg2rad($longitude1);
    //   $rad_lat2 = deg2rad($latitude2);
    //   $rad_long2 = deg2rad($longitude2);

    //   $x = ($rad_long2-$rad_long1) * cos(($rad_lat1+$rad_lat2)/2);
    //   $y = ($rad_lat2-$rad_lat1);

    //   $dist = sqrt($x*$x + $y*$y) * 6371;

    //   return (round($dist,5)); 
    // }

    function fitnessTSP($solution, $distMatrix)
    {
        $distance = 0.0;
        for ($bus = 0; $bus < count($solution) - 1; $bus++) {
            $departureNode = $solution[$bus];
            $nextNode = $solution[$bus + 1];
            $distance += $distMatrix[$departureNode][$nextNode];
        }
        $fitness = 1 / $distance;
        unset($distance, $bus, $departureNode, $nextNode);
        return $fitness;
    }

    function swapSearch($solution)
    {

        //Buat 2 random Node untuk swap Node
        $swap_node =  $this->generateRandomSolution(sizeof($solution) - 2, 2);
        $swap1 = $swap_node[0];
        $swap2 = $swap_node[1];

        //Tukar posisi solusi X dengan Swap 1 dan Swap 2
        $new_solution = $solution;
        $temp = $new_solution[$swap1];
        $new_solution[$swap1] = $new_solution[$swap2];
        $new_solution[$swap2] = $temp;

        return $new_solution;
    }

    function insertSearch($solution)
    {
        //Buat 2 random Node untuk Insert Node
        $insertion_node =  $this->generateRandomSolution(sizeof($solution) - 2, 2);

        //Pastikan node yang di insert bukan node yang bersebelahan
        while (abs($insertion_node[0] - $insertion_node[1]) == 1 || abs($insertion_node[0] - $insertion_node[1]) == 0)
            $insertion_node = $this->generateRandomSolution(sizeof($solution) - 2, 2);

        $insert1 = $insertion_node[0];
        $insert2 = $insertion_node[1];
        $new_solution = $solution;
        $tmp_arr = $solution;

        if ($insert1 < $insert2) {
            $new_solution[$insert2 - 1] = $new_solution[$insert1];
            if ($insert1 == ($insert2 - 2)) {
                $new_solution[$insert1] = $tmp_arr[$insert1 + 1];
            } else {
                $n = $insert1;
                foreach (array_slice($tmp_arr, $insert1 + 1, $insert2 - $insert1 - 1) as $k) {
                    $new_solution[$n] = $k;
                    $n++;
                }
            }
        } else {
            $new_solution[$insert1 - 1] = $new_solution[$insert2];
            if ($insert2 == ($insert1 - 2)) {
                $new_solution[$insert2] = $tmp_arr[$insert2 + 1];
            } else {
                $n = $insert2;
                foreach (array_slice($tmp_arr, $insert2 + 1, $insert1 - $insert2 - 1) as $k) {
                    $new_solution[$n] = $k;
                    $n++;
                }
            }
        }
        return $new_solution;
    }

    function twoOptSearch($solution)
    {

        $edge_node =  $this->generateRandomSolution(sizeof($solution) - 3, 2);


        while (abs($edge_node[0] - $edge_node[1]) == 1 || abs($edge_node[0] - $edge_node[1]) == 0)
            $edge_node =  $this->generateRandomSolution(sizeof($solution) - 3, 2);

        sort($edge_node);

        $edge_A = $edge_node[0];
        $edge_B = $edge_node[0] + 1;
        $edge_C = $edge_node[1];
        $edge_D = $edge_node[1] + 1;

        $new_solution = $solution;
        $node_A = $new_solution[$edge_A];
        $node_B = $new_solution[$edge_C];
        $node_C = $new_solution[$edge_B];
        $node_D = $new_solution[$edge_D];

        $new_solution[$edge_A] = $node_A;
        $new_solution[$edge_B] = $node_B;
        $new_solution[$edge_C] = $node_C;
        $new_solution[$edge_D] = $node_D;

        unset($edge_node, $edge_A, $edge_B, $edge_C, $edge_D, $node_A, $node_B, $node_C, $node_D);
        return $new_solution;
    }

    function getNum(&$v)
    {

        // Size of the vector
        $n = sizeof($v);

        // Generate a random number
        srand(time());

        // Make sure the number is within
        // the index range
        $index = rand() % $n;

        // Get random number from the vector
        $num = $v[$index];

        // Remove the number from the vector
        $t = $v[$index];
        $v[$index] = $v[$n - 1];
        $v[$n - 1] = $t;
        array_pop($v);

        // Return the removed number
        return $num;
    }

    // Function to generate n non-repeating
    // random numbers
    function generateRandomSolution($n, $nn = 0)
    {
        if ($nn == 0)
            $nn = $n;

        $v = array(0, $n, NULL);
        $k = array();

        // Fill the vector with the values
        // 1, 2, 3, ..., n
        for ($i = 0; $i < $n; $i++)
            $v[$i] = $i + 1;

        // While vector has elements
        // get a random number from the
        $n = 0;
        while ($n < $nn) {
            $k[$n] =  $this->getNum($v);
            $n++;
        }
        return $k;
    }


    function get_distance($origins, $dest)
    {
        $key = "AIzaSyCXEykmj3RsEDFNBrLCmA-lmxKCWqT-zCI";
        $ch = curl_init();

        // set url 
        curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/directions/json?origin=" . $origins . "&destination=" . $dest . "&key=" . $key);

        // return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string 
        $output = curl_exec($ch);

        // tutup curl 
        curl_close($ch);
        // menampilkan hasil curl
        $outputdecode = json_decode($output);
        dd($outputdecode['legs']);
        $distance = '';
        $duration = '';
        //   if ((  $outputdecode['legs'] as array).isNotEm) {
        //     final leg = data['legs'][0];
        //     distance = leg['distance']['text'];
        //     duration = leg['duration']['text'];
        //   }
        return json_decode($output)->resourceSets[0]->resources[0]->results[0]->travelDistance;
    }



    public function calculateDistance($lokasi1_lat, $lokasi1_long, $lokasi2_lat, $lokasi2_long, $unit = 'm', $desimal = 2)
    {
        // Menghitung jarak dalam derajat
        $derajat = rad2deg(acos((sin(deg2rad($lokasi1_lat)) * sin(deg2rad($lokasi2_lat))) + (cos(deg2rad($lokasi1_lat)) * cos(deg2rad($lokasi2_lat)) * cos(deg2rad($lokasi1_long - $lokasi2_long)))));

        // Mengkonversi derajat kedalam unit yang dipilih (kilometer, mil atau mil laut)
        switch ($unit) {
            case 'm':
                $jarak = $derajat * 111.13384 * 1000; // 1 derajat = 111.13384 km, berdasarkan diameter rata-rata bumi (12,735 km)
                break;
            case 'mi':
                $jarak = $derajat * 69.05482; // 1 derajat = 69.05482 miles(mil), berdasarkan diameter rata-rata bumi (7,913.1 miles)
                break;
            case 'nmi':
                $jarak = $derajat * 59.97662; // 1 derajat = 59.97662 nautic miles(mil laut), berdasarkan diameter rata-rata bumi (6,876.3 nautical miles)
        }
        return round($jarak, $desimal);
    }
}
