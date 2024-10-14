<?php
//set the time a script should execute  to 24 hours
set_time_limit(86400);

//!!!important!!! 
// create a data folder whithin your project

function sendRequest($filePath) {

    //url to our nodeJs app
    $url = 'http://localhost:3000/print'; 
      //data to be set
    $data = ['filePath' => $filePath];
     //options to be sent with our request
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];
     //send the request
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        // Handle error
        echo "Failed to send print request";

        return false;
    } else {
        echo "Print request sent successfully";

        return true;
    }
}

//this will move my files
function moveFile($file) {

    $source = __DIR__.'/data/' . $file;
    $destination = __DIR__.'/data/finished/' . $file;

    if (rename($source, $destination)) {
        echo "File moved successfully";
    } else {
        echo "Error moving file";
    }
}

//  assign useful data
$directory = __DIR__.'/data/';
$files = scandir($directory);

// get only pdf
$pdfFiles = array_filter($files, function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
});

$canSendRequest=true;

//start printing files
foreach ($pdfFiles as $file) {

    //send each request
    if($canSendRequest){
        //check if the previous request was successfully
        if(sendRequest($directory . $file)){
            $canSendRequest=true;
            //move files in order to not confuse printed and no printed files
            moveFile($file);
        }else{
            //block new printing
            $canSendRequest=false;
        }
    }

    //wait for 5 seconds before printing another document
    sleep(5);
}
