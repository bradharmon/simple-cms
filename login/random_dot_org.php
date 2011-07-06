<?php
function random_dot_org()
{
    #get a random string from random.org
    $curl = curl_init();
    curl_setopt ($curl, CURLOPT_URL, "https://www.random.org/strings/?num=16&len=16&digits=on&upperalpha=on&loweralpha=on&unique=on&format=plain&rnd=new");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec ($curl);
    curl_close ($curl);
    return preg_replace('/\s+/', '', $result);
}
?>
